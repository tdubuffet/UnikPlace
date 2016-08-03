<?php

namespace OrderBundle\Service;

use AppBundle\Service\MangoPayService;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\CurrencyBundle\Currency\Converter;
use MangoPay\Libraries\Exception;
use OrderBundle\Entity\OrderProposal;
use OrderBundle\Event\OrderProposalEvent;
use Symfony\Component\HttpFoundation\Session\Session;
use OrderBundle\Entity\Order;
use OrderBundle\Event\OrderEvents;
use OrderBundle\Event\OrderEvent;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;

/**
 * Class OrderService
 * @package OrderBundle\Service
 */
class OrderService
{
    /** @var EntityManager $em */
    private $em;
    /** @var Converter $currencyConverter */
    private $currencyConverter;
    /** @var TraceableEventDispatcher $eventDispatcher */
    private $eventDispatcher;
    /** @var MangoPayService $mangopayService */
    private $mangopayService;

    /**
     * OrderService constructor.
     * @param EntityManager $em
     * @param Converter $currencyConverter
     * @param TraceableEventDispatcher $eventDispatcher
     * @param MangoPayService $service
     */
    public function __construct($em, $currencyConverter, $eventDispatcher, MangoPayService $service)
    {
        $this->em = $em;
        $this->currencyConverter = $currencyConverter;
        $this->eventDispatcher = $eventDispatcher;
        $this->mangopayService = $service;
    }

    /**
     * @param $user
     * @param $currency
     * @param $preAuthId
     * @return array
     * @throws \Exception
     */
    public function createOrdersFromCartSession($user, $currency, $preAuthId)
    {
        $pendingStatus = $this->em->getRepository('OrderBundle:Status')->findOneByName('pending');

        /**
         * We create an order per product at the moment
         */

        $session        = new Session();
        $cart           = $session->get('cart', array());
        $cartDelivery  = $session->get('cart_delivery', null);

        $addresses      = $session->get('cart_addresses');

        // Fetch products from cart
        $products       = array();
        $orders         = array();


        $acceptedStatus = $this->em
            ->getRepository('OrderBundle:Status')
            ->findOneByName('accepted');


        foreach ($cart as $productId) {
            $product = $this->em->getRepository('ProductBundle:Product')->findOneById($productId);

            $product->setStatus(
                $this->em->getRepository('ProductBundle:Status')->findOneByName('unavailable')
            );

            $orderProposal = $this->em->getRepository('OrderBundle:OrderProposal')->findOneBy([
                'product' => $product,
                'user' => $user,
                'status' => $acceptedStatus
            ]);

            if($orderProposal) {
                $product->setProposalAccepted($orderProposal);
            }

            $productAmount = $this->currencyConverter->convert(
                (!is_null($product->getProposalAccepted())) ? $product->getProposalAccepted()->getAmount() : $product->getPrice(),
                $currency,
                true,
                $product->getCurrency()->getCode()
            );

            // Also add delivery fee to order amount
            if (!isset($cartDelivery[$productId])) {
                throw new \Exception('Not found delivery type');
            }
            $deliveryModeCode = $cartDelivery[$product->getId()];
            $deliveryMode = $this->em->getRepository('OrderBundle:DeliveryMode')->findOneByCode($deliveryModeCode);
            if (!isset($deliveryMode)) {
                throw new \Exception('Delivery mode not found.');
            }
            $delivery = $this->em->getRepository('OrderBundle:Delivery')->findOneBy(['product' => $product, 'deliveryMode' => $deliveryMode]);
            $deliveryAmount = $this->currencyConverter->convert(
                $delivery->getFee(),
                'EUR',
                true,
                $product->getCurrency()->getCode()
            );

            $order = new Order();
            $order->setProductAmount($productAmount);
            $order->setDeliveryAmount($deliveryAmount);
            $order->setAmount($productAmount + $deliveryAmount);
            $order->setDelivery($delivery);
            $order->setCurrency($this->em->getRepository('ProductBundle:Currency')->findOneByCode($currency));
            $order->setUser($user);

            $order->setDeliveryAddress(null);
            if (isset($addresses['delivery_address'])) {
                $deliveryAddress = $this->em->getRepository('LocationBundle:Address')->findOneById(
                    $addresses['delivery_address']
                );
                $order->setDeliveryAddress($deliveryAddress);
            }

            $order->setBillingAddress(null);
            if (isset($addresses['billing_address'])) {
                $billingAddress = $this->em->getRepository('LocationBundle:Address')->findOneById(
                    $addresses['billing_address']
                );
                $order->setBillingAddress($billingAddress);
            }

            $order->setStatus($pendingStatus);
            $order->setMangopayPreauthorizationId($preAuthId);
            $order->setProduct($product);

            $this->em->persist($product);
            $this->em->persist($order);
            $orders[] = $order;
        }


        $this->em->flush();

        foreach ($orders as $order) {
            // Create the OrderEvent and dispatch it
            $event = new OrderEvent($order);
            $this->eventDispatcher->dispatch(OrderEvents::ORDER_CREATED, $event);
        }

        return $orders;
    }

    public function removeCartSession()
    {
        // Remove all session variables setted in cart process
        $session = new Session();
        $session->remove('cart');
        $session->remove('cart_delivery');
        $session->remove('cart_addresses');
        $session->remove('card_registration_id');
        $session->remove('cart_amount');
    }

    /**
     * @param Order $order
     */
    public function cancelOrder(Order $order)
    {
        $updateStatus = false;

        $preAuth = $this->mangopayService->checkStatusPreAuth($order->getMangopayPreauthorizationId());

        if ($preAuth->PayInId == null) {
            $updateStatus = true;
        } else {
            $mangoId = $order->getUser()->getMangopayUserId();
            $refund = $this->mangopayService->refundOrder($mangoId, $preAuth->PayInId, $order->getAmount());
            if ($refund) {
                $updateStatus = true;
                $order->setMangopayRefundId($refund->Id)->setMangopayRefundDate(new \DateTime());
            }
        }

        if ($updateStatus) {
            $statusCanceled = $this->em->getRepository('OrderBundle:Status')->findOneBy(['name' => 'canceled']);
            $order->setStatus($statusCanceled);
            $status = $this->em->getRepository('ProductBundle:Status')->findOneBy(['name' => 'published']);
            $product = $order->getProduct()->setStatus($status);

            $this->eventDispatcher->dispatch(OrderEvents::ORDER_REFUSED, new OrderEvent($order));

            $this->em->persist($order);
            $this->em->persist($product);
            $this->em->flush();
        }
    }

    /**
     * @param Order $order
     */
    public function validateOrder(Order $order)
    {
        $amount = $order->getMangopayPreauthorizationId();
        $totalAmount = $this->em->getRepository('OrderBundle:Order')->getTotalAmount($amount);
        $payInId = $this->mangopayService->createPayIn($order->getUser(), $order, $totalAmount);

        if ($payInId !== false) {
            $order->setMangopayPayinId($payInId)->setMangopayPayinDate(new \DateTime());

            $statusSold = $this->em->getRepository('ProductBundle:Status')->findOneBy(['name' => 'sold']);
            $product = $order->getProduct()->setStatus($statusSold);

            $statusAccepted = $this->em->getRepository('OrderBundle:Status')->findOneBy(['name' => 'accepted']);
            $order->setStatus($statusAccepted);

            $this->em->persist($order);
            $this->em->persist($product);
            $this->em->flush();

            $this->eventDispatcher->dispatch(OrderEvents::ORDER_ACCEPTED, new OrderEvent($order));
        }

    }

    /**
     * @param Order $order
     * @return bool
     */
    public function doneOrder(Order $order)
    {
        try {
            $this->mangopayService->validateOrder($order);

            $statusDone = $this->em->getRepository('OrderBundle:Status')->findOneBy(['name' => 'done']);
            $order->setStatus($statusDone);

            $this->em->persist($order);
            $this->em->flush();

            $this->eventDispatcher->dispatch(OrderEvents::ORDER_DONE, new OrderEvent($order));
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param Order $order
     */
    public function disputeOrder(Order $order)
    {
        $statusDisputed = $this->em->getRepository('OrderBundle:Status')->findOneBy(['name' => 'disputed']);
        $order->setStatus($statusDisputed);

        $this->em->persist($order);
        $this->em->flush();

        $this->eventDispatcher->dispatch(OrderEvents::ORDER_DISPUTE_OPENED, new OrderEvent($order));
    }

    /**
     * @param Order $order
     */
    public function closeDisputeOrder(Order $order)
    {
        $statusAccepted = $this->em->getRepository('OrderBundle:Status')->findOneBy(['name' => 'accepted']);
        $order->setStatus($statusAccepted);

        $this->em->persist($order);
        $this->em->flush();

        $this->eventDispatcher->dispatch(OrderEvents::ORDER_DISPUTE_CLOSED, new OrderEvent($order));
    }

    public function newOrderProposal(OrderProposal $proposal)
    {
        $this->eventDispatcher->dispatch(OrderEvents::ORDER_PROPOSAL_NEW, new OrderProposalEvent($proposal));
    }

    public function changeOrderProposal(OrderProposal $proposal)
    {
        $this->eventDispatcher->dispatch(OrderEvents::ORDER_PROPOSAL_CHANGE, new OrderProposalEvent($proposal));
    }
}