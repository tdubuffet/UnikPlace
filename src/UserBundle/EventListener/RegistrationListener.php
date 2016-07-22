<?php

namespace UserBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Service\MangoPayService;
use Doctrine\ORM\EntityManager;

class RegistrationListener implements EventSubscriberInterface
{
    private $mangopayService;
    private $em;

    public function __construct(MangoPayService $mangopayService, EntityManager $em)
    {
        $this->mangopayService = $mangopayService;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_COMPLETED => 'onUserRegistrationCompleted'
        );
    }

    public function onUserRegistrationCompleted(FilterUserResponseEvent $event)
    {
        // Create the mangopay user for the current user
        $user = $event->getUser();

        // TODO

    }
}