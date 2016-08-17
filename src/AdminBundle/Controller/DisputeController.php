<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 12/08/16
 * Time: 14:35
 */

namespace AdminBundle\Controller;

use FOS\MessageBundle\FormType\ReplyMessageFormType;
use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use OrderBundle\Entity\Order;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class DisputeController extends BaseAdminController
{

    /**
     * @Route(path = "/admin/litiges", name = "admin_disputes")
     * @Template("AdminBundle:Dispute:list.html.twig")
     * @param Request $request
     * @Security("has_role('ROLE_ADMIN')")
     * @return array
     */
    public function disputesAction(Request $request)
    {

        $disputedStatus = $this->getDoctrine()->getRepository('OrderBundle:Status')->findOneBy(['name' => 'disputed']);

        $disputes = $this->getDoctrine()->getRepository('OrderBundle:Order')->findBy(['status' => $disputedStatus], ['createdAt' => 'DESC'], 200);


        return [
            'disputes' => $disputes
        ];
    }


    /**
     * @Route(path = "/admin/litige/{id}", name = "admin_dispute")
     * @Template("AdminBundle:Dispute:dispute.html.twig")
     * @param Request $request
     * @Security("has_role('ROLE_ADMIN')")
     * @return array
     */
    public function disputeAction(Order $order, Request $request)
    {

        $thread = $this->getDoctrine()->getRepository('MessageBundle:Thread')->findThreadByProductAndUsers($order->getProduct(), [
            $order->getUser()->getId(),
            $order->getProduct()->getUser()->getId()
        ]);


        $form = $this->createForm(ReplyMessageFormType::class);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $composer = $this->get('fos_message.composer');

            $message = $composer->reply($thread)
                ->setSender($this->getUser())
                ->setBody($form->getData()['body'])
                ->getMessage();

            $sender = $this->get('fos_message.sender');

            $sender->send($message);

            if ($request->get('refund') !== null) {
                $this->get('order_service')->cancelOrder($order);
                return $this->redirectToRoute('admin_disputes');
            }

            if ($request->get('close') !== null) {
                $this->get('order_service')->closeDisputeOrder($order);
                return $this->redirectToRoute('admin_disputes');
            }

            return $this->redirectToRoute('admin_dispute', ['id' => $order->getId() ]);

        }


        return [
            'order'     => $order,
            'thread'    => $thread,
            'form' => $form->createView()
        ];
    }

}