<?php

namespace CartBundle\Controller;

use CartBundle\Form\SelectCartAddressType;
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
     * @Method({"GET"})
     * @Template("CartBundle:Delivery:deliveryEmc.html.twig")
     */
    public function deliveryAction(Request $request)
    {

        $cart = $this->get('session')
            ->get('cart', array());

        $products = array();
        $productsTotalPrice = 0;

        $deliveries = [];

        /**
         * generate hash delivery cache
         */

        $hash               = md5(implode('-', $cart));

        if ($deliveriesCache = $this->get('app_cache')->fetch($hash)){
            $deliveries = $deliveriesCache;
        }

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

            if(!$deliveriesCache) {
                $deliveries[$product->getId()] = $delivery->findDeliveryByProduct(
                    $this->getUser(),
                    $request->getClientIp(),
                    $product
                );
            }


        }

        if (!$deliveriesCache) {
            $this->get('app_cache')->save($hash, $deliveries);
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
        $session = new Session();
        $cart = $session->get('cart', array());
        if (empty($cart)) {
            // Redirect to homepage if cart is empty
            return $this->redirectToRoute('homepage');
        }

        $address = new Address;
        $addAddressForm = $this->createForm(AddressType::class, $address);
        
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
            'addAddressForm' => $addAddressForm->createView(),
            'selectAddressForm' => $selectAddressForm->createView(),
            'addresses' => $addresses,
            'by_hand_only' => $byHandOnly
        ];
    }

    /**
     * @Route("/adresse")
     * @Method({"POST"})
     */
    public function addressProcessAction(Request $request)
    {
        if ($request->request->has('address')) {

            $address    = new Address;
            $form       = $this->createForm(AddressType::class, $address);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $city = $this->getDoctrine()->getRepository('LocationBundle:City')->findOneById(
                    $request->request->get('address')['city']
                );

                if ($city) {
                    throw new \Exception('Cannot find city.');
                }

                $address->setCity($city);
                $address->setUser(
                    $this->getUser()
                );

                $this->getDoctrine()->getManager()->persist($address);
                $this->getDoctrine()->getManager()->flush();

                $this->get('session')
                    ->getFlashBag()
                    ->add('notice', 'Adresse ajoutée avec succès.');
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
                        ->findOne(
                            [
                                'id' => $address,
                                'user' => $this->getUser()
                            ]
                        );

                    if ($address) {
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