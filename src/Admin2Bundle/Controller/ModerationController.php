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
 * @Route("/moderation")
 */
class ModerationController extends Controller
{
    /**
     * @Route("/", name="ad2_moderation_list")
     */
    public function listAction(Request $request)
    {
        $query = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('u')
            ->from('\ProductBundle\Entity\Product', 'u')
            ->where('u.status = :status')
            ->setParameter('status', 1);

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($query));
        $pagerfanta->setMaxPerPage(10);

        try {
            $pagerfanta->setCurrentPage($request->get('page', 1));
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $this->render('Admin2Bundle:Moderation:list.html.twig', [
            'products' => $pagerfanta
        ]);
    }

    /**
     * @Route("/edit/{id}", name="ad2_moderation_edit")
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


            if ($request->get('accepted', false) !== false) {

                $accepted = $this->getDoctrine()->getRepository('ProductBundle:Status')->findOneByName('published');
                $product->setStatus($accepted);


                $this->get('mailer_sender')->sendAcceptedProductEmailMessage($product);

                $this->getDoctrine()->getManager()->persist($product);

            }

            if ($request->get('refused', false) !== false) {

                $status = $this->getDoctrine()->getRepository('ProductBundle:Status')->findOneByName('refused');
                $product->setStatus($status);

                $this->get('mailer_sender')->sendRefusedProductEmailMessage($product);

                $this->getDoctrine()->getManager()->persist($product);

            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ad2_moderation_list');

        }


        return $this->render('Admin2Bundle:Moderation:edit.html.twig', [
            'product'       => $product,
            'productForm'   => $productForm->createView(),
            'customFields'  => $customFields
        ]);
    }

    /**
     * @Route("/photos/{id}", name="ad2_moderation_photos")
     */
    public function imagesAction(Request $request, Product $product)
    {
        $form = $this->createForm(ImageType::class);
        $form->handleRequest($request);

        $images = [];
        $i = 0;

        foreach($product->getImages() as $img) {
            $img->setSort($i++);
            $images[$img->getId()] = $img;
        }

        if ($form->isValid()) {
            $image = $form->getData();
            $image->setProduct($product);

            $this->getDoctrine()->getManager()->persist($image);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute("ad2_moderation_photos", ['id' => $product->getId()]);
        }

        if ($request->get('sort', false) !== false) {


            $sorts = $request->get('sort', false);

            foreach($sorts as $key => $position) {

                $image = $this->getDoctrine()->getRepository('ProductBundle:Image')->find($key);

                if ($image) {

                    $image->setSort($position);

                    $this->getDoctrine()->getManager()->persist($image);
                }
            }
            $this->getDoctrine()->getManager()->flush();


            return $this->redirectToRoute("ad2_moderation_photos", ['id' => $product->getId()]);
        }

        return $this->render('Admin2Bundle:Moderation:photos.html.twig', [
            'form'          => $form->createView(),
            'product'       => $product,
            'images'        => $images
        ]);
    }

    /**
     * @Route("/photos/remove/{id}", name="ad2_moderation_photos_remove")
     */
    public function removeImageAction(Request $request, Image $image)
    {

        $this->getDoctrine()->getManager()->remove($image);
        $this->getDoctrine()->getManager()->flush();


        return $this->redirectToRoute("ad2_moderation_photos", ['id' => $image->getProduct()->getId()]);
    }
}
