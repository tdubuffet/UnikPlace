<?php
namespace OrderBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OrderBundle\Event\OrderEvents as Event;
use OrderBundle\Event\OrderEvent;
use AppBundle\Service\MailerSender;

class OrderEventsListener implements EventSubscriberInterface
{
    private $mailerSender;

    public function __construct(MailerSender $mailerSender)
    {
        $this->mailerSender = $mailerSender;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Event::ORDER_CREATED => 'onOrderCreated'
        );
    }

    public function onOrderCreated(OrderEvent $event)
    {
        $order = $event->getOrder();
        $this->mailerSender->sendPendingOrderToSellerEmailMessage($order);
    }
}
