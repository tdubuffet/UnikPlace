<?php

namespace Admin2Bundle\Controller;

use Admin2Bundle\Model\AttributesProduct;
use OrderBundle\Entity\Delivery;
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
 * @Route("/moderation")
 */
class ModerationController extends Controller
{
    /**
     * @Route("/", name="ad2_moderation_list")
     * @param Request $request
     * @return Response
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
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $this->render('Admin2Bundle:Moderation:list.html.twig', ['products' => $pagerfanta]);
    }

    /**
     * @Route("/edit/{id}", name="ad2_moderation_edit")
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function editAction(Request $request, Product $product)
    {
        $productForm = $this->createForm(ProductType::class, $product);
        $productForm->handleRequest($request);

        $filters = [];
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
                        $customDelivery = new Delivery();
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
                        $byHandDelivery = new Delivery();
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


            if ($request->get('accepted', false) !== false) {

                $accepted = $this->getDoctrine()->getRepository('ProductBundle:Status')->findOneByName('published');
                $product->setStatus($accepted);

                $this->get('mailer_sender')->sendAcceptedProductEmailMessage($product);
            }

            if ($request->get('refused', false) !== false) {

                $status = $this->getDoctrine()->getRepository('ProductBundle:Status')->findOneByName('refused');
                $product->setStatus($status);

                $this->get('mailer_sender')->sendRefusedProductEmailMessage($product);
            }


            $this->getDoctrine()->getManager()->persist($product);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ad2_moderation_list');

        }

        return $this->render(
            'Admin2Bundle:Moderation:edit.html.twig',
            [
                'product' => $product,
                'productForm' => $productForm->createView(),
                'customFields' => $customFields,
            ]
        );
    }

    /**
     * @Route("/photos/{id}", name="ad2_moderation_photos")
     * @param Request $request
     * @param Product $product
     * @return RedirectResponse|Response
     */
    public function imagesAction(Request $request, Product $product)
    {
        $form = $this->createForm(ImageType::class);
        $form->handleRequest($request);

        $images = [];
        $i = 0;

        foreach ($product->getImages() as $img) {
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

            foreach ($sorts as $key => $position) {

                $image = $this->getDoctrine()->getRepository('ProductBundle:Image')->find($key);

                if ($image) {
                    $image->setSort($position);
                    $this->getDoctrine()->getManager()->persist($image);
                }
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute("ad2_moderation_photos", ['id' => $product->getId()]);
        }

        return $this->render(
            'Admin2Bundle:Moderation:photos.html.twig',
            [
                'form' => $form->createView(),
                'product' => $product,
                'images' => $images,
            ]
        );
    }

    /**
     * @Route("/photos/remove/{id}", name="ad2_moderation_photos_remove")
     * @param Request $request
     * @param Image $image
     * @return RedirectResponse
     */
    public function removeImageAction(Request $request, Image $image)
    {
        $this->getDoctrine()->getManager()->remove($image);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute("ad2_moderation_photos", ['id' => $image->getProduct()->getId()]);
    }
}
