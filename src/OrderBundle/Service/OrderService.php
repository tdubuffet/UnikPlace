<?php

namespace OrderBundle\Service;

use Symfony\Component\HttpFoundation\Session\Session;
use OrderBundle\Entity\Order;
use OrderBundle\Event\OrderEvents;
use OrderBundle\Event\OrderEvent;

class OrderService
{
    private $em;
    private $currencyConverter;
    private $eventDispatcher;

    public function __construct($em, $currencyConverter, $eventDispatcher)
    {
        $this->em = $em;
        $this->currencyConverter = $currencyConverter;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createOrdersFromCartSession($user, $currency, $preAuthId)
    {
        $pendingStatus = $this->em->getRepository('OrderBundle:Status')->findOneByName('pending');

        /**
         * We create an order per product at the moment
         */

        $session        = new Session();
        $cart           = $session->get('cart', array());
        $cartDelivery   = $session->get('cart_delivery', null);

        $addresses = $session->get('cart_addresses');

        // Fetch products from cart
        $products = array();
        $orders = array();
        foreach ($cart as $productId) {
            $product = $this->em->getRepository('ProductBundle:Product')->findOneById($productId);

            $product->setStatus(
                $this->em->getRepository('ProductBundle:Status')->findOneByName('unavailable')
            );

            $amount = $this->currencyConverter->convert($product->getPrice(), $currency, true, $product->getCurrency()->getCode());
            $order = new Order();
            $order->setAmount($amount);
            $order->setCurrency($this->em->getRepository('ProductBundle:Currency')->findOneByCode($currency));
            $order->setUser($user);

            $order->setDeliveryAddress(null);
            if (isset($addresses['delivery_address'])) {
                $deliveryAddress = $this->em->getRepository('LocationBundle:Address')->findOneById($addresses['delivery_address']);
                $order->setDeliveryAddress($deliveryAddress);
            }

            $order->setBillingAddress(null);
            if (isset($addresses['billing_address'])) {
                $billingAddress = $this->em->getRepository('LocationBundle:Address')->findOneById($addresses['billing_address']);
                $order->setBillingAddress($billingAddress);
            }

            $order->setStatus($pendingStatus);
            $order->setMangopayPreauthorizationId($preAuthId);
            $order->setProduct($product);

            if (!isset($cartDelivery[$productId])) {
                throw new \Exception('Not found delivery type');
            }

            $order->setDeliveryType($this->em->getRepository('OrderBundle:Delivery')->findOneByCode($cartDelivery[$productId]));

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
}