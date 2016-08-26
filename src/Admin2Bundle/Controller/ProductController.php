<?php

namespace Admin2Bundle\Controller;

use Admin2Bundle\Model\AttributesProduct;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/product")
 */
class ProductController extends Controller
{
    /**
     * @Route("/", name="ad2_product_list")
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

        return $this->render('Admin2Bundle:Product:list.html.twig', [
            'products' => $pagerfanta
        ]);
    }

    /**
     * @Route("/edit/{id}", name="ad2_product_edit")
     */
    public function editAction(Request $request, Product $product)
    {


        $productForm = $this->createForm(ProductType::class, $product);
        $productForm->handleRequest($request);

        $customFields = (new AttributesProduct($this->get('twig')))->getAttributes($product, $filters);


        if ($productForm->isValid())  {


            foreach($product->getAttributeValues() as $attr) {
                $this->getDoctrine()->getManager()->remove($attr);
            }

            $this->getDoctrine()->getManager()->persist($product);
            $this->getDoctrine()->getManager()->flush();


            foreach($filters as $key => $filter) {

                $value  = $request->get('attribute-' . $key);

                if ($request->get('attribute-' . $key)) {


                    $attributeValue = new AttributeValue();
                    $attributeValue->setProduct($product);

                    $referentialValue = $this->getDoctrine()->getRepository('ProductBundle:ReferentialValue')->find($value);
                    $attributeValue->setReferentialValue($referentialValue);

                    $attribute = $this->getDoctrine()->getRepository('ProductBundle:Attribute')->findOneByCode($key);
                    $attributeValue->setAttribute($attribute);

                    $this->getDoctrine()->getManager()->persist($attributeValue);

                }

            }

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
