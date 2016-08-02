<?php

namespace CartBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class DefaultController
 * @package CartBundle\Controller
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/panier/paiement")
 */
class PaymentController extends Controller
{
    /**
     * @Route("", name="cart_payment")
     * @Method({"GET"})
     * @Template("CartBundle:Payment:payment.html.twig")
     */
    public function paymentAction(Request $request)
    {
        $session = new Session();
        $cart = $session->get('cart', array());

        $acceptedStatus = $this->getDoctrine()
            ->getRepository('OrderBundle:Status')
            ->findOneByName('accepted');

        // Fetch products from cart
        $products               = array();
        $productsTotalPrice     = 0; // in EUR
        $deliveryFee            = 0; // in EUR

        $cartDelivery = $session->get('cart_delivery');
        var_dump($cartDelivery);
        $cartAddresses = $session->get('cart_addresses');

        foreach ($cart as $productId) {
            $product = $this->getDoctrine()
                ->getRepository('ProductBundle:Product')
                ->findOneById($productId);


            $orderProposal = $this->getDoctrine()
                ->getRepository('OrderBundle:OrderProposal')
                ->findOneBy([
                'product' => $product,
                'user' => $this->getUser(),
                'status' => $acceptedStatus
            ]);

            if($orderProposal) {
                $product->setPrice($orderProposal->getAmount());
            }

            $products[] = $product;
            $productsTotalPrice += $this->get('lexik_currency.converter')->convert(
                $product->getPrice(),
                'EUR',
                true,
                $product->getCurrency()->getCode()
            );

            if (!isset($cartDelivery[$productId])) {
                throw new \Exception('Not found delivery type');
            }
            $deliveryModeCode = $cartDelivery[$product->getId()];
            $deliveryMode = $this->getDoctrine()->getRepository('OrderBundle:DeliveryMode')->findOneBy(['code' => $deliveryModeCode]);
            $cartDeliveryFinal[$product->getId()] = $deliveryMode->getName();
            if (!isset($deliveryMode)) {
                throw new \Exception('Delivery mode not found.');
            }
            $delivery = $this->getDoctrine()->getRepository('OrderBundle:Delivery')->findOneBy(['product' => $product, 'deliveryMode' => $deliveryMode]);
            $deliveryFee += $this->get('lexik_currency.converter')->convert(
                $delivery->getFee(),
                'EUR',
                true,
                $product->getCurrency()->getCode()
            );
        }


        if (empty($cartDelivery) || empty($cartAddresses)) {
            $session->getFlashBag()->add('notice', 'Votre panier a expiré. Merci de renouveler l\'opération.');
            return $this->redirectToRoute('cart');
        }

        $addresses = [];
        foreach ($cartAddresses as $addressType => $address) {
            $address = $this->getDoctrine()->getRepository('LocationBundle:Address')->findOneById($address);
            if (!isset($address)) {
                throw new \Exception('Address with id '.$address.' cannot be found.');
            } else {
                if ($address->getUser() != $this->getUser()) {
                    throw new \Exception('Current user does not own address with id '.$address.'.');
                }
            }
            $addresses[$addressType] = $address;
        }

        // Check buyer kyc
        if (!$this->get('mangopay_service')->isKYCValidUser($this->getUser(), $productsTotalPrice, 0)) {
            $this->get('session')->getFlashBag()->add('kyc_errors',
                                                      "Vous avez atteint la limite de " .  $this->container->getParameter('mangopay.max_input') . "€ de crédit ou " . $this->container->getParameter('mangopay.max_output') . "€ de retrait vers votre compte. Afin de valider votre commande ou votre retrait, vous devez renseigner les informations suivantes pour valider votre identité bancaire. Une fois les éléments transmis à notre organisme bancaire, vous pourrez de nouveau valider vos commandes et demander des retraits sur votre compte."
            );
            return $this->redirectToRoute('user_account_wallet_kyc');
        }

        $cardRegistration = $this->get('mangopay_service')->createCardRegistration(
            $this->getUser()->getMangopayUserId(),
            'EUR'
        );
        $session->set('card_registration_id', $cardRegistration->Id);
        $session->set('cart_amount', $productsTotalPrice + $deliveryFee);

        return [
            'products' => $products,
            'productsTotalPrice' => $productsTotalPrice,
            'deliveryFee' => $deliveryFee,
            'deliveryModes' => $cartDeliveryFinal,
            'addresses' => $addresses,
            'cardRegistration' => $cardRegistration
        ];
    }

    /**
     * @Route("/validation", name="cart_payment_validation")
     * @Method({"GET"})
     */
    public function paymentValidationAction(Request $request)
    {
        $session = new Session();
        $get = $request->query->all();
        $mangopayService = $this->get('mangopay_service');

        if ((isset($get['data']) || isset($get['errorCode'])) && $session->has('card_registration_id')) {
            $cardRegistration = $mangopayService->getCardRegistration($session->get('card_registration_id'));
            $cardRegistration->RegistrationData = isset($get['data']) ? 'data='.$get['data'] : 'errorCode='.$get['errorCode'];
            try {
                $updatedCardRegistration = $mangopayService->updateCardRegistration($cardRegistration);
            } catch (\Exception $e) {
                // Card already processed
                $session->getFlashBag()->add('error', "Une erreur s'est produite lors du paiement.");

                return $this->redirectToRoute('cart_payment');
            }
            if ($updatedCardRegistration->Status != 'VALIDATED' || !isset($updatedCardRegistration->CardId)) {
                // Cannot create virtual card. Payment has not been created.
                $session->getFlashBag()->add(
                    'error',
                    "Les informations de paiement ne sont pas valides, veuillez réessayer."
                );

                return $this->redirectToRoute('cart_payment');
            }
            $card = $mangopayService->getCard($updatedCardRegistration->CardId);

            // Create Pre authorization
            $preAuth = $mangopayService->createCardPreAuthorization(
                $this->getUser()->getMangopayUserId(),
                $session->get('cart_amount'),
                'EUR',
                $card,
                $this->generateUrl('cart_payment_secure', [], UrlGeneratorInterface::ABSOLUTE_URL)
            );
            if ($preAuth->Status == 'CREATED' && isset($preAuth->SecureModeRedirectURL)) {
                // Redirect to 3d secure url
                return $this->redirect($preAuth->SecureModeRedirectURL);
            } else {
                $session->getFlashBag()->add('error', "Une erreur s'est produite lors du paiement.");

                return $this->redirectToRoute('cart_payment');
            }
        }

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/secure", name="cart_payment_secure")
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
                $orderService = $this->get('order_service');
                $orders = $orderService->createOrdersFromCartSession(
                    $this->getUser(),
                    'EUR',
                    $preAuth->Id
                );
                $orderService->removeCartSession();

                return $this->redirectToRoute('cart_confirmation');
            }
        }
        $session->getFlashBag()->add('error', "Une erreur s'est produite lors du paiement.");

        return $this->redirectToRoute('cart_payment');
    }
}