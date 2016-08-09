<?php

namespace ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use ProductBundle\Entity\Favorite;

class FavoriteController extends Controller
{

    /**
     * @Route("/favorite", name="product_favorite", options={"expose"=true})
     * @Method({"POST"})
     */
    public function addAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(array('message' => 'You must be authentificated to add product favorite.'), 401);
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
        $repository = $this->getDoctrine()->getRepository('ProductBundle:Favorite');
        $favorite = $repository->findOneBy(array('user' => $user, 'product' => $product));
        $em = $this->getDoctrine()->getManager();
        if ($action == 'add') {
            if (!isset($favorite)) {
                $favorite = new Favorite;
                $favorite->setProduct($product);
                $favorite->setUser($user);
                $em->persist($favorite);
                $em->flush();
            }
            return new JsonResponse(array('message' => 'Favorite added.'), 201);
        }
        else if ($action == 'remove') {
            if (isset($favorite)) {
                $em->remove($favorite);
                $em->flush();
            }
            return new JsonResponse(array('message' => 'Favorite removed.'));
        }
        return new JsonResponse(array('message' => 'An error occured.'), 500);
    }
}