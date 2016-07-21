<?php

namespace OrderBundle\Service;

use Symfony\Component\HttpFoundation\Session\Session;
use OrderBundle\Entity\Order;

class OrderService
{
    private $em;
    private $currencyConverter;

    public function __construct($em, $currencyConverter)
    {
        $this->em = $em;
        $this->currencyConverter = $currencyConverter;
    }

    public function createOrdersFromCartSession($user, $currency, $preAuthId)
    {
        $pendingStatus = $this->em->getRepository('OrderBundle:Status')->findOneByName('pending');

        /**
         * We create an order per product at the moment
         */

        $session = new Session();
        $cart = $session->get('cart', array());
        $addresses = $session->get('cart_addresses');

        // Fetch products from cart
        $products = array();
        foreach ($cart as $productId) {
            $product = $this->em->getRepository('ProductBundle:Product')->findOneById($productId);
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
                $order->setBillingAddress($addresses['billing_address']);
            }
            $order->setStatus($pendingStatus);
            $order->setMangopayPreauthorizationId($preAuthId);
            $order->addProduct($product);
            $this->em->persist($order);
        }
        $this->em->flush();
        return $order;
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