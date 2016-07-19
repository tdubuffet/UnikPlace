<?php

namespace ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

class CartController extends Controller
{

    /**
     * @Route("/cart", name="cart")
     * @Method({"GET"})
     * @Template("ProductBundle:Cart:index.html.twig")
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
        foreach ($cart as $productId) {
            $products[] = $this->getDoctrine()->getRepository('ProductBundle:Product')->findOneById($productId);
        }
        return ['products' => $products];
    }

    /**
     * @Route("/product_cart", name="product_cart")
     * @Method({"POST"})
     */
    public function addAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(array('message' => 'You must be authentificated to add product in cart.'), 401);
        }
        $user = $this->getUser();
        $action = $request->get('action');
        if (!isset($action) && in_array($action, ['add', 'remove'])) {
            return new JsonResponse(array('message' => 'An action must be specified.'), 409);
        }
        $productId = $request->get('product_id');
        if (!isset($productId)) {
            return new JsonResponse(array('message' => 'A product id (product_id) must be specified.'), 409);
        }
        $product = $this->getDoctrine()->getRepository('ProductBundle:Product')->findOneById($productId);
        if (!isset($product)) {
            return new JsonResponse(array('message' => 'Product not found.'), 404);
        }
        if (empty($product->getStatus())) {
            return new JsonResponse(array('message' => 'Product does not have any status.'), 404);
        }
        if ($product->getStatus() && $product->getStatus()->getName() != 'published') {
            return new JsonResponse(array('message' => 'Product not available.'), 404);
        }
        $session = new Session();
        $cart = $session->get('cart', array());
        if ($action == 'add' && !in_array($product->getId(), $cart)) {
            $cart[] = $product->getId();
            $session->set('cart', $cart);
            return new JsonResponse(array('message' => 'Product added in cart.'), 201);
        }
        else if ($action == 'remove') {
            $cart = array_diff($cart, [$product->getId()]);
            $session->set('cart', $cart);
            return new JsonResponse(array('message' => 'Product removed from cart.'));
        }
        return new JsonResponse(array('message' => 'An error occured.'), 500);
    }

    /**
     * @Route("/cart/livraison", name="cart_delivery")
     * @Method({"GET"})
     * @Template("ProductBundle:Cart:delivery.html.twig")
     */
    public function deliveryAction()
    {


    }

    /**
     * @Route("/cart/paiement", name="cart_payment")
     * @Method({"GET"})
     * @Template("ProductBundle:Cart:payment.html.twig")
     */
    public function paymentAction()
    {

    }

    /**
     * @Route("/cart/confirmation", name="cart_confirmation")
     * @Method({"GET"})
     * @Template("ProductBundle:Cart:confirmation.html.twig")
     */
    public function confirmationAction()
    {

    }
}