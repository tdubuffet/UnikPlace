<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 26/08/16
 * Time: 14:36
 */

namespace Admin2Bundle\Controller;


use Admin2Bundle\Form\OrderStatusForm;
use OrderBundle\Entity\Status;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StatusController
 * @package Admin2Bundle\Controller
 * @Route("/order_status")
 */
class OrderStatusController extends Controller
{
    /**
     * @Route("/", name="ad2_order_status_list")
     * @return Response
     */
    public function listStatusAction()
    {
        $twigArray['statusList'] = $this->getDoctrine()->getRepository("OrderBundle:Status")->findAll();
        foreach ($twigArray['statusList'] as $key => $st) {
            $twigArray['count'][$key] = $this->getDoctrine()->getRepository("OrderBundle:Order")->countByStatus($st);
        }

        return $this->render('Admin2Bundle:OrderStatus:list.html.twig', $twigArray);
    }

    /**
     * @Route("/new", name="ad2_order_status_new")
     * @param Request $request
     * @return Response|RedirectResponse
     */
    public function newStatusAction(Request $request)
    {
        $form = $this->createForm(OrderStatusForm::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Status $status */
            $status = $form->getData();
            $this->getDoctrine()->getManager()->persist($status);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash("success", sprintf('Le statut %s a bien été ajouté.', $status->getName()));

            return $this->redirectToRoute('ad2_order_status_list');
        }

        return $this->render('Admin2Bundle:OrderStatus:new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/edit/{id}", name="ad2_order_status_edit")
     * @ParamConverter("status", class="OrderBundle:Status")
     * @param Request $request
     * @param Status $status
     * @return Response|RedirectResponse
     */
    public function editStatusAction(Request $request, Status $status)
    {
        $form = $this->createForm(OrderStatusForm::class, $status);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($status);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash("success", sprintf('Le statut %s a bien été modifié.', $status->getName()));

            return $this->redirectToRoute('ad2_order_status_list');
        }

        return $this->render('Admin2Bundle:OrderStatus:new.html.twig',
            ['form' => $form->createView(), 'status' => $status]);
    }

    /**
     * @Route("/remove", name="ad2_object_remove", options={"expose": "true"})
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function removeObjectAction(Request $request)
    {
       $content = $this->get('my_admin_bundle.delete')->remove($request);

        return new JsonResponse($content[0], $content[1]);
    }

}