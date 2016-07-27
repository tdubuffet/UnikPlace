<?php

namespace AppBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use ProductBundle\Entity\Product;
use MessageBundle\Entity\Message;
use OrderBundle\Entity\Order;

class MailerSender
{
    private $mailer;
    private $router;
    private $templating;
    private $parameters;

    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface $router, \Twig_Environment $twig, array $parameters)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->parameters = $parameters;
    }


    public function sendAcceptedProductEmailMessage(Product $product)
    {
        $template = 'ProductBundle:email:accepted_product.email.twig';
        $user = $product->getUser();
        $context = ['product' => $product, 'user' => $user];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $user->getEmail());
    }

    public function sendRefusedProductEmailMessage(Product $product)
    {
        $template = 'ProductBundle:email:refused_product.email.twig';
        $user = $product->getUser();
        $context = ['product' => $product, 'user' => $user];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $user->getEmail());
    }

    public function sendPrivateMessageNotificationEmailMessage(Message $message)
    {
        $sender = $message->getSender();
        $thread = $message->getThread();
        $participants = $thread->getParticipants();
        $product = $thread->getProduct();
        $threadUrl = $this->router->generate('fos_message_thread_view', ['threadId' => $thread->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        foreach ($participants as $participant) {
            if ($participant->getEmail() != $message->getSender()) {
                $template = 'MessageBundle:email:notification.email.twig';
                $context = ['message' => $message, 'recipient' => $participant, 'sender' => $sender, 'product' => $product, 'threadUrl' => $threadUrl];
                $this->sendMessage($template, $context, $this->parameters['from_email'], $participant->getEmail());
            }
        }
    }

    public function sendPendingOrderToSellerEmailMessage(Order $order)
    {
        $template = 'OrderBundle:email:seller_pending.email.twig';
        $product = $order->getProduct();
        $seller = $product->getUser();
        $orderUrl = $this->router->generate('user_account_sale', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $context = ['order' => $order, 'product' => $product, 'user' => $seller, 'orderUrl' => $orderUrl];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $seller->getEmail());
    }


    private function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        // Add name if from_email is the same as in config
        if ($fromEmail === $this->parameters['from_email']) {
            $fromEmail = [$this->parameters['from_email'] => $this->parameters['from_name']];
        }
        $context = $this->twig->mergeGlobals($context);
        $template = $this->twig->loadTemplate($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);
        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }
        $this->mailer->send($message);
    }
}