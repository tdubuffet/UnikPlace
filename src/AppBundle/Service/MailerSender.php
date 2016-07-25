<?php

namespace AppBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use ProductBundle\Entity\Product;

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


    private function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
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