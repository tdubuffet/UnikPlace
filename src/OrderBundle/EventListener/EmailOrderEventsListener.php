<?php
namespace OrderBundle\EventListener;

use OrderBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OrderBundle\Event\OrderEvents as Event;
use OrderBundle\Event\OrderEvent;
use AppBundle\Service\MailerSender;

class EmailOrderEventsListener implements EventSubscriberInterface
{
    private $mailerSender;

    public function __construct(MailerSender $mailerSender)
    {
        $this->mailerSender = $mailerSender;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Event::ORDER_CREATED => 'onOrderCreated',
            Event::ORDER_ACCEPTED => 'onOrderAccepted',
            Event::ORDER_REFUSED => 'onOrderRefused',
            Event::ORDER_DONE => 'onOrderDone',
            Event::ORDER_DISPUTE_OPENED => 'onOrderDisputeOpened',
            Event::ORDER_DISPUTE_CLOSED => 'onOrderDisputeClosed',
            Event::ORDER_LIMITED_1600 => 'onOrderLimited1600',
            Event::ORDER_LIMITED_2500 => 'onOrderLimited2500',
        );
    }

    public function onOrderCreated(OrderEvent $event)
    {
        $order = $event->getOrder();
        $this->mailerSender->sendPendingOrderToSellerEmailMessage($order);
    }

    public function onOrderAccepted(OrderEvent $event)
    {
        $order = $event->getOrder();
        $this->mailerSender->sendAcceptedOrderToBuyerEmailMessage($order);
    }

    public function onOrderRefused(OrderEvent $event)
    {
        $order = $event->getOrder();
        $this->mailerSender->sendRefusedOrderToBuyerEmailMessage($order);
    }

    public function onOrderDone(OrderEvent $event)
    {
        $order = $event->getOrder();
        $this->mailerSender->sendDoneOrderToSellerEmailMessage($order);
    }

    public function onOrderDisputeOpened(OrderEvent $event)
    {
        $order = $event->getOrder();
        $this->mailerSender->sendOpenedOrderDisputeEmailMessage($order);
    }

    public function onOrderDisputeClosed(OrderEvent $event)
    {
        $order = $event->getOrder();
        $this->mailerSender->sendClosedOrderDisputeEmailMessage($order);
    }

    public function onOrderLimited1600(UserEvent $event)
    {

        $user = $event->getUser();
        $this->mailerSender->sendOrderLimitedKYCEmailMessage($user, 1600);
    }


    public function onOrderLimited2500(UserEvent $event)
    {

        $user = $event->getUser();
        $this->mailerSender->sendOrderLimitedKYCEmailMessage($user, 2500);
    }
}
