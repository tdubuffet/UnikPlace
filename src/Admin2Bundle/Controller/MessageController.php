<?php

namespace Admin2Bundle\Controller;

use FOS\JsRoutingBundle\Controller\Controller;
use FOS\MessageBundle\Entity\Thread;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use ProductBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(path = "/messages")
 */
class MessageController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{

    /**
     * @Route(path = "/", name = "ad2_messages")
     * @Template("Admin2Bundle:Message:list.html.twig")
     */
    public function messagesAction(Request $request)
    {
        $messages = $this->getDoctrine()->getRepository('MessageBundle:Message')->findBy([], ['createdAt' => 'DESC'], 200);


        return [
            'messages' => $messages
        ];
    }

    /**
     * @Route(path = "/thread/{id}", name = "ad2_thread")
     * @Template("Admin2Bundle:Message:thread.html.twig")
     */
    public function threadAction(Request $request, $id)
    {

        $thread = $this->getDoctrine()->getRepository('MessageBundle:Thread')->find($id);

        return [
            'thread' => $thread
        ];
    }
}
