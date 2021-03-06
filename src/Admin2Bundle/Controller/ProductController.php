<?php

namespace Admin2Bundle\Controller;

use Admin2Bundle\Model\AttributesProduct;
use DeliveryBundle\Service\Delivery;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use ProductBundle\Entity\AttributeValue;
use ProductBundle\Entity\Image;
use ProductBundle\Entity\Product;
use ProductBundle\Form\ImageType;
use ProductBundle\Form\ProductType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/product")
 */
class ProductController extends Controller
{
    /**
     * @Route("/", name="ad2_product_list")
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request)
    {
        $search = $request->get('search');

        $query = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('p')
            ->from('\ProductBundle\Entity\Product', 'p')
            ->join('p.user', 'u');

        if ($search) {
            $query->where('p.name LIKE :search or u.username LIKE :search OR p.id LIKE :search')
                ->setParameter('search', "%$search%");
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($query));
        $pagerfanta->setMaxPerPage(50);

        try {
            $pagerfanta->setCurrentPage($request->get('page', 1));
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $this->render('Admin2Bundle:Product:list.html.twig', ['products' => $pagerfanta]);
    }

    /**
     * @Route("/edit/{id}", name="ad2_product_edit")
     * @param Request $request
     * @param Product $product
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, Product $product)
    {
        $productForm = $this->createForm(ProductType::class, $product);
        $productForm->handleRequest($request);

        $customFields = (new AttributesProduct($this->get('twig')))->getAttributes($product, $filters);

        if ($productForm->isValid()) {
            foreach ($product->getAttributeValues() as $attr) {
                $this->getDoctrine()->getManager()->remove($attr);
                $product->removeAttributeValue($attr);
            }

            foreach ($filters as $key => $filter) {
                $value = $request->get('attribute-'.$key);

                if ($request->get('attribute-'.$key)) {
                    $attributeValue = new AttributeValue();
                    $attributeValue->setProduct($product);

                    $referentialValue = $this->getDoctrine()->getRepository('ProductBundle:ReferentialValue')
                        ->find($value);
                    $attributeValue->setReferentialValue($referentialValue);

                    $attribute = $this->getDoctrine()->getRepository('ProductBundle:Attribute')->findOneByCode($key);
                    $attributeValue->setAttribute($attribute);
                    $product->addAttributeValue($attributeValue);

                    $this->getDoctrine()->getManager()->persist($attributeValue);
                }
            }

            // Custom delivery
            $customDeliveryFee = $productForm->get('customDeliveryFee')->getData();
            $customDeliveryMode = $this->getDoctrine()->getRepository('OrderBundle:DeliveryMode')->findOneByCode('seller_custom');
            if (isset($customDeliveryMode)) {
                $customDelivery = $this->getDoctrine()->getRepository('OrderBundle:Delivery')->findOneBy([
                    'product' => $product,
                    'deliveryMode' => $customDeliveryMode
                ]);
                if (isset($customDeliveryFee)) {
                    if (!isset($customDelivery)) {
                        $customDelivery = new \OrderBundle\Entity\Delivery();
                        $customDelivery->setProduct($product);
                        $customDelivery->setDeliveryMode($customDeliveryMode);
                    }
                    $customDelivery->setFee($customDeliveryFee);
                    $this->getDoctrine()->getManager()->persist($customDelivery);
                    $this->getDoctrine()->getManager()->flush();
                } else {
                    if (isset($customDelivery)) {
                        $this->getDoctrine()->getManager()->remove($customDelivery);
                        $this->getDoctrine()->getManager()->flush();
                    }
                }
            }
            // By hand delivery
            $byHandDeliveryEnabled = $productForm->get('byHandDelivery')->getData();
            $byHandDeliveryMode = $this->getDoctrine()->getRepository('OrderBundle:DeliveryMode')->findOneByCode('by_hand');
            if (isset($byHandDeliveryMode)) {
                $byHandDelivery = $this->getDoctrine()->getRepository('OrderBundle:Delivery')->findOneBy([
                    'product' => $product,
                    'deliveryMode' => $byHandDeliveryMode
                ]);
                if ($byHandDeliveryEnabled) {
                    if (!isset($byHandDelivery)) {
                        $byHandDelivery = new \OrderBundle\Entity\Delivery();
                        $byHandDelivery->setProduct($product);
                        $byHandDelivery->setDeliveryMode($byHandDeliveryMode);
                        $byHandDelivery->setFee(0);
                        $this->getDoctrine()->getManager()->persist($byHandDelivery);
                        $this->getDoctrine()->getManager()->flush();
                    }
                } else if (isset($byHandDelivery)) {
                    $this->getDoctrine()->getManager()->remove($byHandDelivery);
                    $this->getDoctrine()->getManager()->flush();
                }
            }

            $this->getDoctrine()->getManager()->persist($product);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ad2_product_list');

        }


        return $this->render('Admin2Bundle:Product:edit.html.twig', [
            'product'       => $product,
            'productForm'   => $productForm->createView(),
            'customFields'  => $customFields
        ]);
    }
}
