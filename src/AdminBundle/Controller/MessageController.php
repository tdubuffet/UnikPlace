<?php

namespace AdminBundle\Controller;

use FOS\MessageBundle\Entity\Thread;
use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use ProductBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;


class MessageController extends BaseAdminController
{

    /**
     * @Route(path = "/admin/messages", name = "admin_messages")
     * @Template("AdminBundle:Message:list.html.twig")
     * @param Request $request
     * @Security("has_role('ROLE_ADMIN')")
     * @return array
     */
    public function messagesAction(Request $request)
    {
        $messages = $this->getDoctrine()->getRepository('MessageBundle:Message')->findBy([], ['createdAt' => 'DESC'], 200);


        return [
            'messages' => $messages
        ];
    }

    /**
     * @Route(path = "/admin/thread/{id}", name = "admin_thread")
     * @Template("AdminBundle:Message:thread.html.twig")
     * @param Request $request
     * @Security("has_role('ROLE_ADMIN')")
     * @return array
     */
    public function threadAction(Request $request, $id)
    {

        $thread = $this->getDoctrine()->getRepository('MessageBundle:Thread')->find($id);

        return [
            'thread' => $thread
        ];
    }
}
