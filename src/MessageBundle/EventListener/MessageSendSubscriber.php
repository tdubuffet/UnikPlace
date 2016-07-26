<?php
namespace MessageBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\MessageBundle\Event\MessageEvent;
use FOS\MessageBundle\Event\FOSMessageEvents as Event;
use FOS\MessageBundle\ModelManager\MessageManagerInterface;
use AppBundle\Service\MailerSender;

class MessageSendSubscriber implements EventSubscriberInterface
{
    private $messageManager;
    private $mailerSender;

    public function __construct(MessageManagerInterface $messageManager, MailerSender $mailerSender)
    {
        $this->messageManager = $messageManager;
        $this->mailerSender = $mailerSender;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Event::POST_SEND => 'onMessageSent'
        );
    }

    public function onMessageSent(MessageEvent $event)
    {
        // Send private message notification email to all participants
        $message = $event->getMessage();
        $this->mailerSender->sendPrivateMessageNotificationEmailMessage($message);
    }
}