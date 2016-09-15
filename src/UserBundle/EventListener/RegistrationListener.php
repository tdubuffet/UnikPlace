<?php

namespace UserBundle\EventListener;

use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use LocationBundle\Entity\Address;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Service\MangoPayService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Router;

class RegistrationListener implements EventSubscriberInterface
{
    private $mangopayService;
    private $em;
    private $router;

    public function __construct(MangoPayService $mangopayService, EntityManager $em, Router $router)
    {
        $this->mangopayService = $mangopayService;
        $this->em = $em;
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_COMPLETED => 'onUserRegistrationCompleted',
            FOSUserEvents::REGISTRATION_CONFIRMED => 'onUserRegistrationConfirmed',
            FOSUserEvents::REGISTRATION_SUCCESS => ['onUserRegistrationSuccess', -1]
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

    public function onUserRegistrationSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();

        if ($user->getPro()) {

            $city = $this->em->getRepository('LocationBundle:City')->find(
                $event->getRequest()->request->get('city_code')
            );

            if (!$city) {
                throw new NotFoundHttpException();
            }

            $street = $event->getRequest()->request->get('fos_user_registration_form')['address']['street'];

            $address = new Address();
            $address->setName($user->getCompanyCode());
            $address->setStreet($street);
            $address->setCity($city);
            $address->setUser($user);

            $user->setCompanyAddress($street);
            $user->setCompanyZipcode($city->getZipcode());
            $user->setCompanyCity($city->getName());
            $user->addAddress($address);
        }


        $response = new RedirectResponse(
            $this->router->generate('homepage')
        );

        if ($user->getPro()) {
            $response = new RedirectResponse(
                $this->router->generate('sell_category')
            );
        }

        $event->setResponse($response);

    }

    public function onUserRegistrationConfirmed(FilterUserResponseEvent $event) {

        $user = $event->getUser();
        $user->setEmailValidated(true);

        // Flush user
        $this->em->persist($user);
        $this->em->flush();
    }
}