<?php

namespace CartBundle\Controller;

use CartBundle\Form\SelectCartAddressType;
use Doctrine\Common\Util\Debug;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use LocationBundle\Entity\Address;
use LocationBundle\Form\AddressType;

/**
 * Class DeliveryController
 * @package CartBundle\Controller
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/panier")
 */
class DeliveryController extends Controller
{

    /**
     * @Route("/livraison", name="cart_delivery_emc")
     * @Method({"GET", "POST"})
     * @Template("CartBundle:Delivery:deliveryEmc.html.twig")
     */
    public function deliveryAction(Request $request)
    {

        $cart = $this->get('session')
            ->get('cart', array());

        $products = array();
        $productsTotalPrice = 0;

        /**
         * generate hash delivery cache
         */

        $hash               = md5(implode('-', $cart)) . '-cart-user-' . $this->getUser()->getId();

        $deliveries         = $this->get('app_cache')->fetch($hash);
        $deliveries = false;

        $modes = $request->get('deliveryMode', []);

        if (!$deliveries){


            $deliveries = [];

            foreach ($cart as $productId) {
                $product = $this->getDoctrine()->getRepository('ProductBundle:Product')->findOneById($productId);

                $orderProposal = $this->getDoctrine()->getRepository('OrderBundle:OrderProposal')->findOneBy([
                    'product' => $product,
                    'user' => $this->getUser(),
                    'status' => 'accepted'
                ]);

                if($orderProposal) {
                    $product->setPrice($orderProposal->getAmount());
                }

                $products[] = $product;
                $productsTotalPrice += $this->get('lexik_currency.converter')
                    ->convert($product->getPrice(), 'EUR', true, $product->getCurrency()->getCode());

                $delivery = $this->get('delivery.emc');

                $addresses      = $this->get('session')->get('cart_addresses');

                if (isset($addresses['delivery_address'])) {
                    $deliveryAddress = $this->getDoctrine()->getRepository('LocationBundle:Address')->findOneById(
                        $addresses['delivery_address']
                    );
                }

                $deliveries[$product->getId()] = $delivery->findDeliveryByProduct(
                    $this->getUser(),
                    $deliveryAddress,
                    $request->getClientIp(),
                    $product
                );
            }

            $this->get('app_cache')->save($hash, $deliveries);
        }

        if ($modes) {

            foreach( $modes as $key => $mode ) {

                if ($mode != 'by_hand' && $mode != 'seller_custom' && !isset($deliveries[$key][$mode])) {
                    return $this->redirectToRoute('cart_delivery_emc');
                }
            }

            $this->get('session')->set('cart_delivery_emc', $deliveries);
            $this->get('session')->set('cart_delivery', $modes);

            return $this->redirectToRoute('cart_payment');
        }


        return [
            'products'              => $products,
            'productsTotalPrice'    => $productsTotalPrice,
            'deliveriesByProduct'            => $deliveries
        ];
    }

    /**
     * @Route("/addresse", name="cart_delivery")
     * @Method({"GET"})
     * @Template("CartBundle:Delivery:delivery.html.twig")
     */
    public function addressAction(Request $request)
    {
        $session = $this->get('session');
        $cart = $session->get('cart', array());
        if (empty($cart)) {
            // Redirect to homepage if cart is empty
            return $this->redirectToRoute('homepage');
        }

        $addressForm = $this->get('user.address_form')->getForm(
            $request,
            $this->getUser(),
            true
        );
        
        $addresses = $this->getDoctrine()
            ->getRepository("LocationBundle:Address")
            ->findBy(
                [
                    'user' => $this->getUser()
                ], 
                [
                    'id' => 'DESC'
                ]
            );
        
        $selectAddressForm = $this->createForm(SelectCartAddressType::class, null, ['addresses' => $addresses]);

        $cartDelivery = $session->get('cart_delivery', array());
        
        $byHandOnly = true;
        foreach ($cartDelivery as $deliveryCode) {
            if ($deliveryCode != 'by_hand') {
                $byHandOnly = false;
            }
        }

        return [
            'addAddressForm' => $addressForm->createView(),
            'selectAddressForm' => $selectAddressForm->createView(),
            'addresses' => $addresses,
            'by_hand_only' => $byHandOnly
        ];
    }

    /**
     * @Route("/addresse")
     * @Method({"POST"})
     */
    public function addressProcessAction(Request $request)
    {
        if ($request->request->has('address')) {

            $addressForm = $this->get('user.address_form')->getForm(
                $request,
                $this->getUser(),
                true
            );

            if ($addressForm === true) {
                $this->addFlash('success', 'L\'adresse a bien été ajoutée');
            }
        } else if ($request->request->has('select_cart_address')) {

            $addresses  = $this->getUser()->getAddresses();
            $form       = $this->createForm(SelectCartAddressType::class, null, ['addresses' => $addresses]);
            $form->handleRequest($request);

            // Save selected addresses
            $addresses = [
                'delivery_address'  => $form['delivery_address']->getData(),
                'billing_address'   => $form['billing_address']->getData()
            ];

            // Make sure addresses are owned by current user
            foreach ($addresses as $address) {

                if (!is_null($address)) {
                    
                    $address = $this->getDoctrine()
                        ->getRepository('LocationBundle:Address')
                        ->findOneBy(
                            [
                                'id' => $address,
                                'user' => $this->getUser()
                            ]
                        );

                    if (!$address) {
                        throw new \Exception('Address ' . $address . ' cannot be found.');
                    }
                }
            }
            $session = new Session();
            $session->set('cart_addresses', $addresses);

            return $this->redirectToRoute('cart_delivery_emc');
        }
        return $this->redirectToRoute('cart_delivery');
    }
}