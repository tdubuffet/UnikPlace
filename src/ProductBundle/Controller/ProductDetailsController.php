<?php

namespace ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use ProductBundle\Entity\Product;

class ProductDetailsController extends Controller
{

    /**
     * @Route("/p/{id}-{slug}", name="product_details")
     * @ParamConverter("product", class="ProductBundle:Product")
     * @Template("ProductBundle:ProductDetails:index.html.twig")
     */
    public function indexAction(Request $request, Product $product)
    {
        $productAttributeService = $this->get('product_bundle.product_attribute_service');
        $attributes              = $productAttributeService->getAttributesFromProduct($product);

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {

            $favorite = $this->getDoctrine()
                ->getRepository('ProductBundle:Favorite')
                ->findOneBy(array(
                    'user' => $this->getUser(),
                    'product' => $product
                ));
        }

        $similarProducts = $this
            ->getDoctrine()
            ->getRepository('ProductBundle:Product')
            ->findSimilarProducts($product, 7);


        /**
         * contact message
         */

        if($this->getUser() && $product->getUser() != $this->getUser()) {
            $existThread = $this->getDoctrine()
                ->getRepository('MessageBundle:Thread')
                ->findExistsThreadByProductAndUser($product, $this->getUser());
        }

        if (isset($existThread) && !$existThread) {
            $process = $this->get('app.message')->processSentProductMessage($request, $product);

            if ($process === true) {
                //Reset request
                return $this->redirectToRoute('product_details', [
                    'id' => $product->getId(),
                    'slug' => $product->getSlug()
                ]);
            }
        }

        return [
            'product' => $product,
            'productAttributes'     => $attributes,
            'isFavorite'            => isset($favorite),
            'similarProducts'       => $similarProducts,
            'thread'                => (isset($existThread)) ? $existThread : false,
            'formMessage'           => (isset($process) && $process !== true) ? $process->createView() : false
        ];
    }
}
