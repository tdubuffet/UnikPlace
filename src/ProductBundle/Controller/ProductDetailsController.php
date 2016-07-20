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

        if($product->getUser() != $this->getUser()) {
            $existThread = $this->getDoctrine()
                ->getRepository('MessageBundle:Thread')
                ->findExistsThreadByProductAndUser($product, $this->getUser());
        }

        $similarProducts = $this
            ->getDoctrine()
            ->getRepository('ProductBundle:Product')
            ->findSimilarProducts($product, 7);


        /**
         * contact message
         */
        $formMessage = $this->createForm(\MessageBundle\Form\NewThreadMessageFormType::class);
        $formMessage->handleRequest($request);

        if ($formMessage->isValid()) {

            $data           = $formMessage->getData();
            $sender         = $this->getUser();
            $threadSender   = $this->get('fos_message.sender');
            $threadBuilder  = $this->get('fos_message.composer_product')->newThread();

            $message = $threadBuilder
                ->addRecipient($product->getUser())
                ->setSender($sender)
                ->setSubject($data['subject'])
                ->setBody($data['body'])
                ->setProduct($product)
                ->getMessage();


            $threadSender->send($message);


            $this->get('session')->getFlashBag()->add('success', 'Message envoyé au vendeur.');

            //Reset request
            return $this->redirectToRoute('product_details', [
                'id' => $product->getId(),
                'slug' => $product->getSlug()
            ]);
        }

        return [
            'product' => $product,
            'productAttributes'     => $attributes,
            'isFavorite'            => isset($favorite),
            'similarProducts'       => $similarProducts,
            'thread'                => (isset($existThread)) ? $existThread : false,
            'formMessage'           => $formMessage->createView()
        ];
    }
}
