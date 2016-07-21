<?php

namespace CartBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{

    /**
     * @Route("/cart", name="cart")
     * @Method({"GET"})
     * @Template("CartBundle:Default:index.html.twig")
     */
    public function listAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('fos_user_security_login');
        }

        $session = new Session();
        $cart = $session->get('cart', array());

        // Fetch products from cart
        $products = array();
        $productsTotalPrice = 0; // in EUR
        $deliveryFee        = 0; // in EUR

        foreach ($cart as $productId) {
            $product = $this->getDoctrine()
                ->getRepository('ProductBundle:Product')
                ->findOneById($productId);
            $products[] = $product;
            $productsTotalPrice += $this->get('lexik_currency.converter')->convert($product->getPrice(), 'EUR', true, $product->getCurrency()->getCode());
        }

        $deliveries = $this->getDoctrine()->getRepository('OrderBundle:Delivery')->findAll();

        return [
            'products'              => $products,
            'productsTotalPrice'    => $productsTotalPrice,
            'deliveryFee'           => $deliveryFee,
            'deliveries'            => $deliveries
        ];
    }

    /**
     * @Route("/cart")
     * @Method({"POST"})
     */
    public function listProcessAction(Request $request)
    {
        // Process delivery modes chosen
        $data = $request->request->all();
        $session = new Session();
        $cart = $session->get('cart', array());
        // Make sure delivery modes are associated with products in cart

        $deliveriesType = $this->getDoctrine()->getRepository('OrderBundle:Delivery')->findAllCode();

        foreach ($data as $productId => $delivery) {
            if (!in_array($productId, $cart) && in_array($delivery, $deliveriesType)) {
                throw new \Exception('Product id '.$productId.' is not associated with product in cart.');
            }
        }
        $cartDelivery = $session->set('cart_delivery', $data);
        return $this->redirectToRoute('cart_delivery');
    }

    /**
     * @Route("/cart/confirmation", name="cart_confirmation")
     * @Method({"GET"})
     * @Template("CartBundle:Default:confirmation.html.twig")
     */
    public function confirmationAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }
}