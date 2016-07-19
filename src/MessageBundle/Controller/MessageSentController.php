<?php

namespace MessageBundle\Controller;

use MessageBundle\Form\NewThreadMessageFormType;
use ProductBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MessageSentController extends Controller
{
    /**
     * @Route("/contacter-le-vendeur/{id}", name="fos_message_thread_new")
     * @Template("MessageBundle:Message:newThread.html.twig")
     */
    public function sentAction(Request $request, Product $product)
    {

        $recipient = $product->getUser();

        $form = $this->createForm(NewThreadMessageFormType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $data           = $form->getData();
            $sender         = $this->getUser();
            $threadSender   = $this->get('fos_message.sender');
            $threadBuilder  = $this->get('fos_message.composer_product')->newThread();

            $message = $threadBuilder
                ->addRecipient($recipient)
                ->setSender($sender)
                ->setSubject($data['subject'])
                ->setBody($data['body'])
                ->setProduct($product)
                ->getMessage();


            $threadSender->send($message);
        }

        return [
            'form' => $form->createView(),
            'data' => $form->getData()
        ];
    }
}
