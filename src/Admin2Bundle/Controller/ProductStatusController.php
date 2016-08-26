<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 26/08/16
 * Time: 14:36
 */

namespace Admin2Bundle\Controller;


use Admin2Bundle\Form\ProductStatusForm;
use ProductBundle\Entity\Status;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProductStatusController
 * @package Admin2Bundle\Controller
 * @Route("/product_status")
 */
class ProductStatusController extends Controller
{
    /**
     * @Route("/", name="ad2_product_status_list")
     * @return Response
     */
    public function listStatusAction()
    {
        $twigArray['statusList'] = $this->getDoctrine()->getRepository("ProductBundle:Status")->findAll();
        foreach ($twigArray['statusList'] as $key => $st) {
            $twigArray['count'][$key] = $this->getDoctrine()->getRepository("ProductBundle:Product")->countByStatus($st);
        }

        return $this->render('Admin2Bundle:ProductStatus:list.html.twig', $twigArray);
    }

    /**
     * @Route("/new", name="ad2_product_status_new")
     * @param Request $request
     * @return Response|RedirectResponse
     */
    public function newStatusAction(Request $request)
    {
        $form = $this->createForm(ProductStatusForm::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Status $status */
            $status = $form->getData();
            $this->getDoctrine()->getManager()->persist($status);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash("success", sprintf('Le statut %s a bien été ajouté.', $status->getName()));

            return $this->redirectToRoute('ad2_product_status_list');
        }

        return $this->render('Admin2Bundle:ProductStatus:new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/edit/{id}", name="ad2_product_status_edit")
     * @ParamConverter("status", class="ProductBundle:Status")
     * @param Request $request
     * @param Status $status
     * @return Response|RedirectResponse
     */
    public function editStatusAction(Request $request, Status $status)
    {
        $form = $this->createForm(ProductStatusForm::class, $status);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($status);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash("success", sprintf('Le statut %s a bien été modifié.', $status->getName()));

            return $this->redirectToRoute('ad2_product_status_list');
        }

        return $this->render('Admin2Bundle:OrderStatus:new.html.twig',
            ['form' => $form->createView(), 'status' => $status]);
    }


}