<?php

namespace ProductBundle\Controller;

use OrderBundle\Entity\OrderProposal;
use OrderBundle\Form\OrderProposalForm;
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
     * @param Request $request
     * @param Product $product
     * @return array
     */
    public function indexAction(Request $request, Product $product)
    {
        $productAttributeService = $this->get('product_bundle.product_attribute_service');
        $attributes = $productAttributeService->getAttributesFromProduct($product);
        $routeparams = ['id' => $product->getId(), 'slug' => $product->getSlug()];

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $favorite = $this->getDoctrine()->getRepository('ProductBundle:Favorite')
                ->findOneBy(['user' => $this->getUser(), 'product' => $product]);
        }

        $similarProducts = $this->getDoctrine()->getRepository('ProductBundle:Product')
            ->findSimilarProducts($product, 7);


        /** * contact message */
        if ($this->getUser() && $product->getUser() != $this->getUser()) {
            $existThread = $this->getDoctrine()->getRepository('MessageBundle:Thread')
                ->findExistsThreadByProductAndUser($product, $this->getUser());
        }

        if (isset($existThread) && !$existThread) {
            $process = $this->get('app.message')->processSentProductMessage($request, $product);

            if ($process === true) {
                //Reset request
                return $this->redirectToRoute('product_details', $routeparams);
            }
        }
        $proposal = $this->getDoctrine()->getRepository('OrderBundle:OrderProposal')
            ->findOneBy(['user' => $this->getUser(), 'product' => $product], ['id' => 'DESC']);
        $limit = $this->getDoctrine()->getRepository('OrderBundle:OrderProposal')->findUserLimit($this->getUser());
        $offLimit = $limit >= 3;

        if (!$proposal || in_array($proposal->getStatus()->getName(), ['canceled']) && !$offLimit) {
            $proposal = new OrderProposal();
            $price = (int)$product->getPrice();
            $proposalForm = $this->createForm(OrderProposalForm::class, $proposal, ['max' => $price]);
            $proposalForm->handleRequest($request);
            if ($proposalForm->isSubmitted() && $proposalForm->isValid()) {
                $status = $this->getDoctrine()->getRepository('OrderBundle:Status')->findOneBy(['name' => 'pending']);
                $proposal->setUser($this->getUser())->setProduct($product)->setStatus($status);
                $this->getDoctrine()->getManager()->persist($proposal);
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', 'Votre offre a bien été prise en compte');
                $this->get('mailer_sender')->sendOrderProposalToSeller($proposal);
                $this->get('order_service')->newOrderProposal($proposal);
                //Reset request
                return $this->redirectToRoute('product_details', $routeparams);
            }
        }

        $ratings = $this->getDoctrine()->getRepository('UserBundle:Rating')->findBy([
            'ratedUser' => $product->getUser(),
            'type' => 'seller'
        ], ['createdAt' => 'DESC'], 10);

        return [
            'product' => $product,
            'productAttributes' => $attributes,
            'isFavorite' => isset($favorite),
            'similarProducts' => $similarProducts,
            'thread' => (isset($existThread)) ? $existThread : false,
            'formMessage' => (isset($process) && $process !== true) ? $process->createView() : false,
            'proposalForm' => isset($proposalForm) ? $proposalForm->createView() : null,
            'proposal' => $proposal,
            'offLimit' => $offLimit,
            'ratings'  => $ratings
        ];
    }
}
