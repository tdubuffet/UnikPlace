<?php

namespace CartBundle\Controller;

use ProductBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

class AjaxController extends Controller
{

    /**
     * @Route("/product_cart", name="product_cart", options={"expose"=true})
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function addAction(Request $request)
    {
        $user = $this->getUser();
        $action = $request->get('action');
        if (!isset($action) && in_array($action, ['add', 'remove'])) {
            return new JsonResponse(array('message' => 'An action must be specified.'), 409);
        }
        $productId = $request->get('product_id');
        if (!isset($productId)) {
            return new JsonResponse(array('message' => 'A product id (product_id) must be specified.'), 409);
        }
        /** @var Product $product */
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

        if ($product->getUser() == $user) {
            return new JsonResponse(array('message' => 'You can not add/remove your product'), 404);
        }

        $session = new Session();
        $cart = $session->get('cart', []);
        $cartQuantity = $session->get('cart_quantity', []);
        if ($action == 'add' && !in_array($product->getId(), $cart)) {
            $cart[] = $product->getId();
            $cartQuantity[$product->getId()] = 1;
            $session->set('cart', $cart);
            $session->set('cart_quantity', $cartQuantity);

            return new JsonResponse(array('message' => 'Product added in cart.'), 201);
        }
        else if ($action == 'remove') {
            $cart = array_diff($cart, [$product->getId()]);
            $session->set('cart', $cart);
            $prices = $this->getCartPrices();

            return new JsonResponse(['message' => 'Product removed from cart.', 'prices' => $prices]);
        }
        return new JsonResponse(array('message' => 'An error occured.'), 500);
    }

    /**
     * @param string $currency
     * @return array
     */
    private function getCartPrices($currency = 'EUR')
    {
        $cart = $this->get('session')->get('cart', []);
        $productsTotalPrice = 0; // in EUR

        foreach ($cart as $productId) {
            $product = $this->getDoctrine()->getRepository('ProductBundle:Product')->findOneBy(['id' => $productId]);
            $offer = $this->getDoctrine()->getRepository("OrderBundle:OrderProposal")
                ->findOneBy(['product' => $product, 'status' => 2]);
            $price = $offer ? $offer->getAmount() : $product->getPrice();



            $cartQuantity = $this->get('session')->get('cart_quantity', []);

            $productsTotalPrice += $this->get('lexik_currency.converter')
                ->convert(($price * $cartQuantity[$product->getId()]), $currency, true, $product->getCurrency()->getCode());
        }
        $service = $this->get('lexik_currency.formatter');

        return ['formated' => $service->format($productsTotalPrice, $currency), 'price' => $productsTotalPrice];
    }

    /**
     * @Route("/product/cart/quantity/{id}/{action}", name="product_cart_quantity", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function quantityAction(Request $request, Product $product, $action = 'add')
    {
        $cart = $this->get('session')->get('cart', []);
        $cartQuantity = $this->get('session')->get('cart_quantity', []);

        if (!in_array($product->getId(), $cart) || !isset($cartQuantity[$product->getId()])) {
            return new JsonResponse(array('message' => 'Product added in cart.'), 201);
        }

        if ($action == 'add') {
            $quantity = $cartQuantity[$product->getId()];
            if ($quantity < $product->getQuantity()) {
                $cartQuantity[$product->getId()]++;
            } else {
                $this->get('session')->set('cart_quantity', $cartQuantity);
                return new JsonResponse([
                    'status' => 'NOK',
                    'message' => 'Vous avez atteint la quantité maximum pour ce produit.',
                    'quantity' => $cartQuantity[$product->getId()],
                    'prices' => $this->getCartPrices()
                ]);
            }
        }

        if ($action == 'remove') {
            if ($cartQuantity[$product->getId()] > 1) {
                $cartQuantity[$product->getId()]--;
            }
        }

        $this->get('session')->set('cart_quantity', $cartQuantity);

        return new JsonResponse([
            'status' => 'OK',
            'quantity' => $cartQuantity[$product->getId()],
            'prices' => $this->getCartPrices()
        ]);
    }
}