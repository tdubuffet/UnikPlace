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

class DeliveryController extends Controller
{

    /**
     * @Route("/cart/livraison", name="cart_delivery")
     * @Method({"GET"})
     * @Template("CartBundle:Delivery:delivery.html.twig")
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
}