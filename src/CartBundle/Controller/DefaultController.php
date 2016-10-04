<?php

namespace CartBundle\Controller;

use ProductBundle\Entity\Product;
use ProductBundle\Entity\Status;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
* Class DefaultController
* @package CartBundle\Controller
*
* @Security("has_role('ROLE_USER')")
* @Route("/panier")
*/
class DefaultController extends Controller
{

    /**
     * @Route("", name="cart")
     * @Method({"GET"})
     * @Template("CartBundle:Default:index.html.twig")
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function listAction(Request $request)
    {
        $session = new Session();
        $cart = $session->get('cart', array());
        /** @var Status $publishedStatus */
        $publishedStatus = $this->getDoctrine()->getRepository('ProductBundle:Status')->findOneByName('published');
        $acceptedStatus = $this->getDoctrine()->getRepository('OrderBundle:Status')->findOneByName('accepted');
        $publishedProducts = array();

        // Fetch products from cart
        $products = array();
        $productsTotalPrice = 0; // in EUR

        foreach ($cart as $productId) {
            /** @var Product $product */
            $product = $this->getDoctrine()->getRepository('ProductBundle:Product')->findOneById($productId);

            $orderProposal = $this->getDoctrine()->getRepository('OrderBundle:OrderProposal')->findOneBy([
                'product' => $product,
                'user' => $this->getUser(),
                'status' => $acceptedStatus
            ]);

            if($orderProposal) {
                $product->setPrice($orderProposal->getAmount());
            }

            $products[] = $product;
            $productsTotalPrice += $this->get('lexik_currency.converter')
                ->convert($product->getPrice(), 'EUR', true, $product->getCurrency()->getCode());
            if ($product->getStatus()->getName() == $publishedStatus->getName()) {
                $publishedProducts[] = $product->getId();
            }
        }

        // Make sure products are still published in cart
        if (count($publishedProducts) != count($cart)) {
            $session->getFlashBag()->add('notice', 'Votre panier a été modifié car certains produits ne sont plus disponibles');
            $session->set('cart', $publishedProducts);
            return $this->redirectToRoute('cart');
        }

        return [
            'products'              => $products,
            'productsTotalPrice'    => $productsTotalPrice,
        ];
    }

    /**
     * @Route("")
     * @Method({"POST"})
     */
    public function listProcessAction(Request $request)
    {
        // Process delivery modes chosen
        $data = $request->request->all();

        $cart = $this->get('session')
            ->get('cart', []);

        $deliveryModes = $this->getDoctrine()
            ->getRepository('OrderBundle:DeliveryMode')
            ->findAllCode();

        foreach ($data as $productId => $delivery) {

            if (!in_array($productId, $cart) && in_array($delivery, $deliveryModes)) {
                throw new \Exception('Product id '.$productId.' is not associated with product in cart.');
            }

        }
        $this->get('session')
            ->set('cart_delivery', $data);

        return $this->redirectToRoute('cart_delivery');
    }

    /**
     * @Route("/confirmation", name="cart_confirmation")
     * @Method({"GET"})
     * @Template("CartBundle:Default:confirmation.html.twig")
     */
    public function confirmationAction(Request $request)
    {
    }
}