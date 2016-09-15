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
            FOSUserEvents::REGISTRATION_COMPLETED => 'onUserRegistrationCompleted',
            FOSUserEvents::REGISTRATION_CONFIRMED => 'onUserRegistrationConfirmed'
        );
    }

    public function onUserRegistrationCompleted(FilterUserResponseEvent $event)
    {
        // Create the mangopay user for the current user
        $user = $event->getUser();
        if ($user->getPro() === true) {
            $mangopayUser = $this->mangopayService->createLegalUser($user);
        }
        else {
            $mangopayUser = $this->mangopayService->createNaturalUser($user);
        }

        //Enabled account
        $user->setEnabled(true);

        // Also create wallets
        $wallets = $this->mangopayService->createWallets($mangopayUser->Id);

        // Put mangopay user id and wallets in user entity
        $user->setMangopayUserId($mangopayUser->Id);
        $user->setMangopayBlockedWalletId($wallets['blocked']->Id);
        $user->setMangopayFreeWalletId($wallets['free']->Id);

        // Flush user
        $this->em->persist($user);
        $this->em->flush();
    }

    public function onUserRegistrationConfirmed(FilterUserResponseEvent $event) {

        $user = $event->getUser();
        $user->setEmailValidated(true);

        // Flush user
        $this->em->persist($user);
        $this->em->flush();
    }
}