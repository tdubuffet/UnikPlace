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
        $attributes = $productAttributeService->getAttributesFromProduct($product);
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $favorite = $this->getDoctrine()->getRepository('ProductBundle:Favorite')->findOneBy(array('user' => $this->getUser(), 'product' => $product));
        }

        $qb = $this->getDoctrine()->getRepository('ProductBundle:Product')->createQueryBuilder('p');
        $qb->where('p.id != :id')->setParameter('id', $product->getId())
           ->andWhere('p.category = :category_id')->setParameter('category_id', $product->getCategory()->getId());
        $similarProducts = $qb->getQuery()->getResult();

        return ['product' => $product, 'productAttributes' => $attributes, 'isFavorite' => isset($favorite), 'similarProducts' => $similarProducts];
    }
}
