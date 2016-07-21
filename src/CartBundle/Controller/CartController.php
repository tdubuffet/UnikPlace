<?php

namespace CartBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use LocationBundle\Entity\Address;
use LocationBundle\Form\AddressType;
use CartBundle\Form\selectCartAddressType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CartController extends Controller
{

    /**
     * @Route("/cart", name="cart")
     * @Method({"GET"})
     * @Template("CartBundle:Cart:index.html.twig")
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
     * @Route("/cart/livraison", name="cart_delivery")
     * @Method({"GET"})
     * @Template("CartBundle:Cart:delivery.html.twig")
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
                    throw new \Exception('Cannot find city.');
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
            $addresses = $this->getUser()->getAddresses();
            $form = $this->createForm(selectCartAddressType::class, null, ['addresses' => $addresses]);
            $form->handleRequest($request);
            // Save selected addresses
            $addresses = [];
            $addresses['delivery_address'] = $form['delivery_address']->getData();
            $addresses['billing_address'] = $form['billing_address']->getData();
            if ($addresses['delivery_address'] == $addresses['billing_address']) {
                unset($addresses['billing_address']);
            }
            // Make sure addresses are owned by current user
            foreach ($addresses as $address) {
                $address = $this->getDoctrine()->getRepository('LocationBundle:Address')->findOneById($address);
                if (!isset($address)) {
                    throw new \Exception('Address with id '.$address.' cannot be found.');
                }
                else if ($address->getUser() != $this->getUser()) {
                    throw new \Exception('Current user does not own address with id '.$address.'.');
                }
            }
            $session = new Session();
            $session->set('cart_addresses', $addresses);
            return $this->redirectToRoute('cart_payment');
        }
        return $this->redirectToRoute('cart_delivery');
    }


    /**
     * @Route("/cart/paiement", name="cart_payment")
     * @Method({"GET"})
     * @Template("CartBundle:Cart:payment.html.twig")
     */
    public function paymentAction(Request $request)
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
        $deliveryModes = $session->get('cart_delivery');
        $cartAddresses = $session->get('cart_addresses');
        $addresses = [];
        foreach ($cartAddresses as $addressType => $address) {
            $address = $this->getDoctrine()->getRepository('LocationBundle:Address')->findOneById($address);
            if (!isset($address)) {
                throw new \Exception('Address with id '.$address.' cannot be found.');
            }
            else if ($address->getUser() != $this->getUser()) {
                throw new \Exception('Current user does not own address with id '.$address.'.');
            }
            $addresses[$addressType] = $address;
        }

        $cardRegistration = $this->get('mangopay_service')->createCardRegistration($this->getUser()->getMangopayUserId(), 'EUR');
        $session->set('card_registration_id', $cardRegistration->Id);
        $session->set('cart_amount', $productsTotalPrice + $deliveryFee);

        return ['products' => $products,
                'productsTotalPrice' => $productsTotalPrice,
                'deliveryFee' => $deliveryFee,
                'deliveryModes' => $deliveryModes,
                'addresses' => $addresses,
                'cardRegistration' => $cardRegistration];
    }

    /**
     * @Route("/cart/paiement/validation", name="cart_payment_validation")
     * @Method({"GET"})
     */
    public function paymentValidationAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('fos_user_security_login');
        }

        $session = new Session();
        $get = $request->query->all();
        $mangopayService = $this->get('mangopay_service');

        if ((isset($get['data']) || isset($get['errorCode'])) && $session->has('card_registration_id')) {
            $cardRegistration = $mangopayService->getCardRegistration($session->get('card_registration_id'));
            $cardRegistration->RegistrationData = isset($get['data']) ? 'data=' . $get['data'] : 'errorCode=' . $get['errorCode'];
            try {
                $updatedCardRegistration = $mangopayService->updateCardRegistration($cardRegistration);
            }
            catch (\Exception $e) {
                // Card already processed
                $session->getFlashBag()->add('error', "Une erreur s'est produite lors du paiement.");
                return $this->redirectToRoute('cart_payment');
            }
            if ($updatedCardRegistration->Status != 'VALIDATED' || !isset($updatedCardRegistration->CardId)) {
                // Cannot create virtual card. Payment has not been created.
                $session->getFlashBag()->add('error', "Les informations de paiement ne sont pas valides, veuillez réessayer.");
                return $this->redirectToRoute('cart_payment');
            }
            $card = $mangopayService->getCard($updatedCardRegistration->CardId);

            // Create Pre authorization
            $preAuth =$mangopayService->createCardPreAuthorization(
                $this->getUser()->getMangopayUserId(),
                $session->get('cart_amount'),
                'EUR',
                $card,
                $this->generateUrl('cart_payment_secure', [], UrlGeneratorInterface::ABSOLUTE_URL)
            );
            if ($preAuth->Status == 'CREATED' && isset($preAuth->SecureModeRedirectURL)) {
                // Redirect to 3d secure url
                return $this->redirect($preAuth->SecureModeRedirectURL);
            }
            else  {
                $session->getFlashBag()->add('error', "Une erreur s'est produite lors du paiement.");
                return $this->redirectToRoute('cart_payment');
            }
        }
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/cart/paiement/secure", name="cart_payment_secure")
     * @Method({"GET"})
     */
    public function paymentSecureAction(Request $request)
    {
        // Process after the validation from 3D secure
        // 3d secure preauthorizationid
        $session = new Session();
        $get = $request->query->all();
        $mangopayService = $this->get('mangopay_service');
        if (isset($get['preAuthorizationId'])) {
            $preAuth = $mangopayService->getCardPreAuthorization($get['preAuthorizationId']);
            if ($preAuth->Status == 'SUCCEEDED' && $preAuth->AuthorId == $this->getUser()->getMangopayUserId()) {
                // Success - Create order and redirect to confirmation page
                /*
                $orderService = $this->get('order_service');
                $order = $orderService->createOrderFromCartSession();
                $orderService->removeCartSession();
                $session->set('last_order', $order);
                */
                return $this->redirectToRoute('cart_confirmation');
            }
        }
        $session->getFlashBag()->add('error', "Une erreur s'est produite lors du paiement.");
        return $this->redirectToRoute('cart_payment');
    }

    /**
     * @Route("/cart/confirmation", name="cart_confirmation")
     * @Method({"GET"})
     * @Template("CartBundle:Cart:confirmation.html.twig")
     */
    public function confirmationAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }
}