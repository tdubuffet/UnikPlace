<?php

namespace AppBundle\Service;

use OrderBundle\Entity\OrderProposal;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use ProductBundle\Entity\Product;
use MessageBundle\Entity\Message;
use OrderBundle\Entity\Order;
use UserBundle\Entity\User;

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
        $context = [
            'product' => $product,
            'user' => $user
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $user->getEmail());
    }

    private function sendMessage($templateName, $context, $fromEmail, $toEmail, $replyToEmail = null)
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
        if (!is_null($replyToEmail)) {
            $message->setReplyTo($replyToEmail);
        }
        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }
        $this->mailer->send($message);
    }

    public function sendRefusedProductEmailMessage(Product $product)
    {
        $template = 'ProductBundle:email:refused_product.email.twig';
        $user = $product->getUser();
        $context = [
            'product' => $product,
            'user' => $user
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $user->getEmail());
    }

    public function sendContactEmailMessage(array $context)
    {
        $template = "AppBundle:email:contact.email.twig";

        $this->sendMessage($template, $context, $this->parameters['from_email'], $this->parameters['contact_email'], $context['email']);
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
                $context = [
                    'message' => $message,
                    'recipient' => $participant,
                    'sender' => $sender,
                    'product' => $product,
                    'threadUrl' => $threadUrl
                ];
                $this->sendMessage($template, $context, $this->parameters['from_email'], $participant->getEmail());
            }
        }
    }

    public function sendOrderSummary($orders, User $buyer)
    {
        $template = 'OrderBundle:email:summary.email.twig';
        $context = [
            'orders' => $orders,
            'user' => $buyer
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $buyer->getEmail());
    }

    public function sendPendingOrderToSellerEmailMessage(Order $order)
    {
        $template = 'OrderBundle:email:seller_pending.email.twig';
        $product = $order->getProduct();
        $seller = $product->getUser();
        $orderUrl = $this->router->generate('user_account_sale', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $context = [
            'order' => $order,
            'product' => $product,
            'user' => $seller,
            'orderUrl' => $orderUrl
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $seller->getEmail());
    }

    public function sendAcceptedOrderToSellerEmailMessage(Order $order)
    {
        $template = 'OrderBundle:email:recall_order_accepted.email.twig';
        $product = $order->getProduct();
        $seller = $product->getUser();
        $orderUrl = $this->router->generate('user_account_sale', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $context = [
            'order' => $order,
            'product' => $product,
            'user' => $seller,
            'orderUrl' => $orderUrl
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $seller->getEmail());
    }

    public function sendAcceptedOrderToBuyerEmailMessage(Order $order)
    {
        $template = 'OrderBundle:email:accepted.email.twig';
        $product = $order->getProduct();
        $buyer = $order->getUser();
        $orderUrl = $this->router->generate('user_account_purchase',
            ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $productUrl = $this->router->generate('product_details',
            [
                'id' => $product->getId(),
                'slug' => $product->getSlug()
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        $context = [
            'order' => $order,
            'product' => $product,
            'user' => $buyer,
            'orderUrl' => $orderUrl,
            'productUrl' => $productUrl
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $buyer->getEmail());
    }

    public function sendRefusedOrderToBuyerEmailMessage(Order $order)
    {
        $template = 'OrderBundle:email:refused.email.twig';
        $product = $order->getProduct();
        $buyer = $order->getUser();
        $context = [
            'order' => $order,
            'product' => $product,
            'user' => $buyer
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $buyer->getEmail());
    }

    public function sendDoneOrderToSellerEmailMessage(Order $order)
    {
        $template = 'OrderBundle:email:done.email.twig';
        $product = $order->getProduct();
        $seller = $product->getUser();
        $walletUrl = $this->router->generate('user_account_wallet', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $context = [
            'order' => $order,
            'product' => $product,
            'user' => $seller,
            'walletUrl' => $walletUrl
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $seller->getEmail());
    }

    public function sendOpenedOrderDisputeEmailMessage(Order $order)
    {
        $template = 'OrderBundle:email:dispute_opened.email.twig';
        $product = $order->getProduct();
        $seller = $product->getUser();
        $buyer = $order->getUser();
        $orderSellUrl = $this->router->generate('user_account_sale', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $orderPurchaseUrl = $this->router->generate('user_account_purchase', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        // Seller
        $context = [
            'order' => $order,
            'product' => $product,
            'user' => $seller,
            'orderSellUrl' => $orderSellUrl,
            'context' => 'sell'
        ];

        $this->sendMessage($template, $context, $this->parameters['from_email'], $seller->getEmail());

        // Buyer
        $context = [
            'order' => $order,
            'product' => $product,
            'user' => $buyer,
            'orderPurchaseUrl' => $orderPurchaseUrl,
            'context' => 'purchase'
        ];

        $this->sendMessage($template, $context, $this->parameters['from_email'], $seller->getEmail());


        // Admin
        $context = [
            'order' => $order,
            'product' => $product,
            'context' => 'admin'
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $this->parameters['contact_email']);
    }

    public function sendClosedOrderDisputeEmailMessage(Order $order)
    {
        $template = 'OrderBundle:email:dispute_closed.email.twig';
        $product = $order->getProduct();
        $seller = $product->getUser();
        $buyer = $order->getUser();
        $orderSellUrl = $this->router->generate('user_account_sale', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $orderPurchaseUrl = $this->router->generate('user_account_purchase', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        // Seller
        $context = [
            'order' => $order,
            'product' => $product,
            'user' => $seller,
            'orderSellUrl' => $orderSellUrl,
            'context' => 'sell'
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $seller->getEmail());

        // Buyer
        $context = [
            'order' => $order,
            'product' => $product,
            'user' => $buyer,
            'orderPurchaseUrl' => $orderPurchaseUrl,
            'context' => 'purchase'
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $seller->getEmail());


        // Admin
        $context = [
            'order' => $order,
            'product' => $product,
            'context' => 'admin'
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $this->parameters['contact_email']);
    }

    public function sendKYCValidatedEmailMessage(User $user, $type)
    {
        $template = 'UserBundle:email:kyc_validated.email.twig';
        $context = [
            'user' => $user,
            'type' => $type
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $user->getEmail());
    }

    public function sendKYCFailedEmailMessage(User $user, $type)
    {
        $template = 'UserBundle:email:kyc_failed.email.twig';
        $context = [
            'user' => $user,
            'type' => $type
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $user->getEmail());
    }

    public function sendOrderProposalToSeller(OrderProposal $proposal)
    {
        $template = 'OrderBundle:email:proposal_to_seller.email.twig';
        $product = $proposal->getProduct();
        $proposalUrl = $this->router->generate('offer_validation',
            ['id' => $proposal->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $productUrl = $this->router->generate('product_details',
            [
                'id' => $product->getId(),
                'slug' => $product->getSlug()
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        $context = [
            'product' => $product,
            'proposal' => $proposal,
            'productUrl' => $productUrl,
            'proposalUrl' => $proposalUrl
        ];
        $email = $proposal->getProduct()->getUser()->getEmail();
        $this->sendMessage($template, $context, $this->parameters['from_email'], $email);
    }

    public function sendOrderProposalToBuyerAccepted(OrderProposal $proposal)
    {
        $template = 'OrderBundle:email:proposal_to_buyer_accepted.email.twig';
        $product = $proposal->getProduct();
        $productUrl = $this->router->generate('product_details',
            [
                'id' => $product->getId(),
                'slug' => $product->getSlug()
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        $context = [
            'product' => $product,
            'proposal' => $proposal,
            'productUrl' => $productUrl
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $proposal->getUser()->getEmail());
    }

    public function sendOrderProposalToBuyerRefused(OrderProposal $proposal)
    {
        $template = 'OrderBundle:email:proposal_to_buyer_refused.email.twig';
        $product = $proposal->getProduct();
        $productUrl = $this->router->generate('product_details',
            [
                'id' => $product->getId(),
                'slug' => $product->getSlug()
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        $context = [
            'product' => $product,
            'proposal' => $proposal,
            'productUrl' => $productUrl
        ];
        $this->sendMessage($template, $context, $this->parameters['from_email'], $proposal->getUser()->getEmail());
    }

    public function sendValidatedEmcToSeller(Order $order)
    {

        $template = 'OrderBundle:emc:seller_validated.email.twig';
        $product = $order->getProduct();
        $seller = $product->getUser();

        $orderUrl = $this->router->generate('user_account_sale', [
            'id' => $order->getId()
        ], UrlGeneratorInterface::ABSOLUTE_URL );

        $context = [
            'order' => $order,
            'product' => $product,
            'user' => $seller,
            'orderUrl' => $orderUrl
        ];

        $this->get('mailer')->s($template, $context, $this->parameters['from_email'], $seller->getEmail());
    }

    public function sendTransitEmcToBuyer(Order $order)
    {

        $template = 'OrderBundle:emc:buyer_transit.email.twig';
        $product = $order->getProduct();
        $buyer = $order->getUser();

        $orderUrl = $this->router->generate('user_account_sale', [
            'id' => $order->getId()
        ], UrlGeneratorInterface::ABSOLUTE_URL );

        $context = [
            'order' => $order,
            'product' => $product,
            'user' => $buyer,
            'orderUrl' => $orderUrl
        ];

        $this->get('mailer')->s($template, $context, $this->parameters['from_email'], $buyer->getEmail());
    }


    public function sendArrivalEmcToBuyer(Order $order)
    {

        $template = 'OrderBundle:emc:buyer_arrival.email.twig';
        $product = $order->getProduct();
        $buyer = $order->getUser();

        $orderUrl = $this->router->generate('user_account_sale', [
            'id' => $order->getId()
        ], UrlGeneratorInterface::ABSOLUTE_URL );

        $context = [
            'order' => $order,
            'product' => $product,
            'user' => $buyer,
            'orderUrl' => $orderUrl
        ];

        $this->get('mailer')->s($template, $context, $this->parameters['from_email'], $buyer->getEmail());
    }
}