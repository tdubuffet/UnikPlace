<?php
namespace OrderBundle\EventListener;

use OrderBundle\Event\OrderProposalEvent;
use OrderBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OrderBundle\Event\OrderEvents as Event;
use OrderBundle\Event\OrderEvent;
use AppBundle\Service\MailerSender;
use UserBundle\Service\NotificationService;

class NotificationOrderEventsListener implements EventSubscriberInterface
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Event::ORDER_CREATED          => 'onOrderCreated',
            Event::ORDER_ACCEPTED         => 'onOrderAccepted',
            Event::ORDER_REFUSED          => 'onOrderRefused',
            Event::ORDER_DONE             => 'onOrderDone',
            Event::ORDER_DISPUTE_OPENED   => 'onOrderDisputeOpened',
            Event::ORDER_DISPUTE_CLOSED   => 'onOrderDisputeClosed',
            Event::ORDER_PROPOSAL_NEW     => 'onOrderProposalNew',
            Event::ORDER_PROPOSAL_CHANGE     => 'onOrderProposalChange',
            Event::ORDER_LIMITED_2500 => 'onOrderLimited2500',
            Event::ORDER_LIMITED_1600 => 'onOrderLimited1600',
        );
    }

    public function onOrderCreated(OrderEvent $event)
    {
        $order = $event->getOrder();

        $this->notificationService->createNotification(
            $order->getProduct()->getUser(),
            'order_pending',
            [
                'order_id' => $order->getId(),
                'product_title' => $order->getProduct()->getName()
            ]
        );
    }

    public function onOrderAccepted(OrderEvent $event)
    {
        $order = $event->getOrder();
        $this->notificationService->createNotification(
            $order->getUser(),
            'order_accepted',
            [
                'order_id' => $order->getId(),
                'product_title' => $order->getProduct()->getName()
            ]
        );
    }

    public function onOrderRefused(OrderEvent $event)
    {
        $order = $event->getOrder();
        $this->notificationService->createNotification(
            $order->getUser(),
            'order_canceled',
            [
                'order_id' => $order->getId(),
                'product_title' => $order->getProduct()->getName()
            ]
        );
    }

    public function onOrderDone(OrderEvent $event)
    {
        $order = $event->getOrder();
        $this->notificationService->createNotification(
            $order->getProduct()->getUser(),
            'order_done',
            [
                'order_id' => $order->getId(),
                'product_title' => $order->getProduct()->getName()
            ]
        );

        $this->notificationService->createNotification(
            $order->getProduct()->getUser(),
            'order_done_wallet_transfer',
            [
                'product_title' => $order->getProduct()->getName()
            ]
        );
    }

    public function onOrderDisputeOpened(OrderEvent $event)
    {
        $order = $event->getOrder();

        $this->notificationService->createNotification(
            $order->getUser(),
            'order_disputed_open_buyer',
            [
                'order_id' => $order->getId(),
                'product_title' => $order->getProduct()->getName()
            ]
        );

        $this->notificationService->createNotification(
            $order->getProduct()->getUser(),
            'order_disputed_open_seller',
            [
                'product_title' => $order->getProduct()->getName(),
                'order_id' => $order->getId()
            ]
        );
    }

    public function onOrderDisputeClosed(OrderEvent $event)
    {
        $order = $event->getOrder();


        $this->notificationService->createNotification(
            $order->getUser(),
            'order_disputed_closed_buyer',
            [
                'order_id' => $order->getId(),
                'product_title' => $order->getProduct()->getName()
            ]
        );

        $this->notificationService->createNotification(
            $order->getProduct()->getUser(),
            'order_disputed_closed_seller',
            [
                'product_title' => $order->getProduct()->getName(),
                'order_id' => $order->getId()
            ]
        );
    }

    public function onOrderProposalNew(OrderProposalEvent $event)
    {
        $proposal = $event->getProposal();
        $this->notificationService->createNotification(
            $proposal->getProduct()->getUser(),
            'order_proposal_new',
            [
                'product_title' => $proposal->getProduct()->getName(),
                'proposal_id' => $proposal->getId()
            ]
        );
    }

    public function onOrderProposalChange(OrderProposalEvent $event)
    {
        $proposal = $event->getProposal();
        $this->notificationService->createNotification(
            $proposal->getUser(),
            'order_proposal_change',
            [
                'product_title' => $proposal->getProduct()->getName(),
                'product_id' => $proposal->getProduct()->getId(),
                'product_slug' => $proposal->getProduct()->getSlug(),
                'change' => $proposal->getStatus()->getName() == "accepted" ? "acceptée" : "refusée"
            ]
        );

    }

    public function onOrderLimited1600(UserEvent $event)
    {
        $this->notificationService->createNotification(
            $event->getUser(),
            'kyc_limit'
        );
    }

    public function onOrderLimited2500(UserEvent $event)
    {
        $this->notificationService->createNotification(
            $event->getUser(),
            'kyc_limit'
        );
    }
}
