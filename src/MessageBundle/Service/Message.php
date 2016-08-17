<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 21/07/16
 * Time: 09:32
 */

namespace MessageBundle\Service;


use FOS\MessageBundle\FormType\NewThreadMessageFormType;
use ProductBundle\Entity\Product;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

class Message
{

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Sent Message since a product
     *
     * @param Request $request
     * @param Product $product
     * @return bool|\Symfony\Component\Form\FormInterface
     * @throws \Exception
     */
    public function processSentProductMessage(Request $request, Product $product, $recipient)
    {
        $formMessage = $this->container
            ->get('form.factory')
            ->create(\MessageBundle\Form\NewThreadMessageFormType::class);

        $formMessage->handleRequest($request);

        if ($formMessage->isValid()) {

            $data           = $formMessage->getData();
            $sender         = $this->container->get('security.token_storage')->getToken()->getUser();
            $threadSender   = $this->container->get('fos_message.sender');
            $threadBuilder  = $this->container->get('fos_message.composer_product')->newThread();


            $message = $threadBuilder
                ->addRecipient($recipient)
                ->setSender($sender)
                //->setSubject($data['subject'])
                ->setBody($data['body'])
                ->setProduct($product)
                ->getMessage();


            $threadSender->send($message);


            $this->container->get('session')->getFlashBag()->add('success', 'Message envoyé avec succès.');

            return true;
        }

        return $formMessage;

    }

    /**
     * Sent direct message
     *
     * @param Request $request
     * @param User $recipient
     * @return bool|\Symfony\Component\Form\FormInterface
     * @throws \Exception
     */
    public function processSentMessage(Request $request, User $recipient)
    {
        $formMessage = $this->container
            ->get('form.factory')
            ->create(NewThreadMessageFormType::class);

        $formMessage->handleRequest($request);

        if ($formMessage->isValid()) {

            $data           = $formMessage->getData();
            $sender         = $this->container->get('security.token_storage')->getToken()->getUser();
            $threadSender   = $this->container->get('fos_message.sender');
            $threadBuilder  = $this->container->get('fos_message.composer')->newThread();

            $message = $threadBuilder
                ->addRecipient($recipient)
                ->setSender($sender)
                ->setSubject($data['subject'])
                ->setBody($data['body'])
                ->getMessage();


            $threadSender->send($message);


            $this->container->get('session')->getFlashBag()->add('success', 'Message envoyé.');

            return true;
        }

        return $formMessage;

    }

}