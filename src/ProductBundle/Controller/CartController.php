<?php

namespace ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use LocationBundle\Entity\Address;
use LocationBundle\Form\AddressType;
use ProductBundle\Form\selectCartAddressType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
        $productsTotalPrice = 0; // in EUR
        $deliveryFee = 0; // in EUR
        foreach ($cart as $productId) {
            $product = $this->getDoctrine()->getRepository('ProductBundle:Product')->findOneById($productId);
            $products[] = $product;
            $productsTotalPrice += $this->get('lexik_currency.converter')->convert($product->getPrice(), 'EUR', true, $product->getCurrency()->getCode());
        }
        return ['products' => $products, 'productsTotalPrice' => $productsTotalPrice, 'deliveryFee' => $deliveryFee];
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
        foreach ($data as $productId => $delivery) {
            if (!in_array($productId, $cart) && in_array($delivery, ['by_hand', 'parcel'])) {
                throw new \Exception('Product id '.$productId.' is not associated with product in cart.');
            }
        }
        $cartDelivery = $session->set('cart_delivery', $data);
        return $this->redirectToRoute('cart_delivery');
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
    public function deliveryAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('fos_user_security_login');
        }
        $address = new Address;
        $addAddressForm = $this->createForm(AddressType::class, $address);
        $addresses = $this->getUser()->getAddresses();
        $selectAddressForm = $this->createForm(selectCartAddressType::class, null, ['addresses' => $addresses]);
        return ['addAddressForm' => $addAddressForm->createView(),
                'selectAddressForm' => $selectAddressForm->createView(),
                'addresses' => $addresses];
    }

    /**
     * @Route("/cart/livraison")
     * @Method({"POST"})
     */
    public function deliveryProcessAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('fos_user_security_login');
        }

        if($request->request->has('address')) {
            $address = new Address;
            $form = $this->createForm(AddressType::class, $address);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $zipcode = $form['city_code']->getData();
                // Get city from zipcode
                $city = $this->getDoctrine()->getRepository('LocationBundle:City')->findOneByZipcode($zipcode);
                if (!isset($city)) {
                    throw new \exception('Cannot find city.');
                }
                $address->setCity($city);
                $address->setUser($this->getUser());
                $em = $this->getDoctrine()->getManager();
                $em->persist($address);
                $em->flush();
                $session = new Session();
                $session->getFlashBag()->add('notice', 'Adresse ajoutée avec succès.');
            }
        }
        else if ($request->request->has('select_cart_address')) {
            // Save selected addresses
            // TODO
            exit();
        }
        return $this->redirectToRoute('cart_delivery');
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