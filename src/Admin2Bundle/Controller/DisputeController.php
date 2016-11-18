<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 12/08/16
 * Time: 14:35
 */

namespace Admin2Bundle\Controller;

use FOS\MessageBundle\FormType\ReplyMessageFormType;
use OrderBundle\Entity\Order;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(path = "/admin/litiges")
 */
class DisputeController extends Controller
{

    /**
     * @Route(path = "/", name = "ad2_disputes")
     * @Template("Admin2Bundle:Dispute:list.html.twig")
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
     * @Route(path = "/{id}", name = "ad2_dispute")
     * @Template("Admin2Bundle:Dispute:dispute.html.twig")
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
                return $this->redirectToRoute('ad2_disputes');
            }

            if ($request->get('close') !== null) {
                $this->get('order_service')->closeDisputeOrder($order);
                return $this->redirectToRoute('ad2_disputes');
            }

            return $this->redirectToRoute('ad2_dispute', ['id' => $order->getId() ]);

        }


        return [
            'order'     => $order,
            'thread'    => $thread,
            'form' => $form->createView()
        ];
    }

}