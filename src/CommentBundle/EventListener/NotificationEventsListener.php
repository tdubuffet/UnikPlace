<?php
namespace CommentBundle\EventListener;

use CommentBundle\Event\CommentEvent;
use CommentBundle\Event\CommentEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OrderBundle\Event\OrderEvent;
use UserBundle\Service\NotificationService;

class NotificationEventsListener implements EventSubscriberInterface
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public static function getSubscribedEvents()
    {
        return array(
            CommentEvents::PRODUCT_COMMENT              => 'onComment',
            CommentEvents::PRODUCT_COMMENT_REPLY        => 'onCommentReply',
        );
    }

    public function onComment(CommentEvent $event)
    {
        $comment = $event->getComment();

        $this->notificationService->createNotification(
            $comment->getThread()->getProduct()->getUser(),
            'comment_new',
            [
                'product_id'    => $comment->getThread()->getProduct()->getId(),
                'product_slug'  => $comment->getThread()->getProduct()->getSlug(),
                'product_title' => $comment->getThread()->getProduct()->getName()
            ]
        );
    }

    public function onCommentReply(CommentEvent $event)
    {
        $comment = $event->getComment();

        $this->notificationService->createNotification(
            $comment->getParent()->getUser(),
            'comment_reply',
            [
                'product_id'    => $comment->getThread()->getProduct()->getId(),
                'product_slug'  => $comment->getThread()->getProduct()->getSlug(),
                'product_title' => $comment->getThread()->getProduct()->getName()
            ]
        );
    }
}
