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
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Router;
use UserBundle\Service\AddressForm;

class RegistrationListener implements EventSubscriberInterface
{
    private $mangopayService;
    private $em;
    private $router;

    public function __construct(MangoPayService $mangopayService, EntityManager $em, Router $router, AddressForm $af)
    {
        $this->mangopayService = $mangopayService;
        $this->em = $em;
        $this->router = $router;
        $this->addressForm = $af;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_COMPLETED => 'onUserRegistrationCompleted',
            FOSUserEvents::REGISTRATION_CONFIRMED => 'onUserRegistrationConfirmed',
            FOSUserEvents::REGISTRATION_CONFIRM => ['onUserRegistrationConfirm', -1],
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

        $session = new Session();
        $session->getFlashBag()->clear();


    }

    public function onUserRegistrationSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();

        if ($user->getPro()) {
            $street = $event->getRequest()->request->get('fos_user_registration_form');


            $cityName = $street['locality'];
            $cityZipCode = $street['postal_code'];

            $city = $this->em
                ->getRepository('LocationBundle:City')
                ->findOneBy(['name' => $cityName, 'zipcode' => $cityZipCode]);

            if (!$city) {

                $city = new City();
                $city->setName($cityName);
                $city->setZipcode($cityZipCode);

                $this->em->persist($city);
                $this->em->flush();
            }

            $address = new Address();
            $address->setFirstname($user->getFirstname());
            $address->setLastname($user->getLastname());
            $address->setStreet($street['street_number'] . ' ' . $street['route']. ' ' . $street['sublocality_level_1']);
            $address->setCity($city);
            $address->setUser($user);

            $address = $this->addressForm->formatedAddress($address);

            $user->setCompanyAddress($street['street_number'] . ' ' . $street['route']. ' ' . $street['sublocality_level_1']);
            $user->setCompanyZipcode($city->getZipcode());
            $user->setCompanyCity($city->getName());
            $user->addAddress($address);
        }

        if ($event->getRequest()->get('phone-full')) {
            $user->setPhone($event->getRequest()->get('phone-full'));
        }

        if ($event->getRequest()->get('redirect', false) && $event->getRequest()->get('redirect')) {
            $response = new RedirectResponse(
                $event->getRequest()->get('redirect')
            );
        } else {
            $response = new RedirectResponse(
                $this->router->generate('homepage')
            );
        }

        if ($user->getPro()) {
            $response = new RedirectResponse(
                $this->router->generate('sell_category')
            );
        }

        $session = new Session();
        $session->getFlashBag()->clear();

        $event->setResponse($response);

    }

    public function onUserRegistrationConfirmed(FilterUserResponseEvent $event) {

        $user = $event->getUser();
        $user->setEmailValidated(true);

        // Flush user
        $this->em->persist($user);
        $this->em->flush();
    }

    public function onUserRegistrationConfirm(GetResponseUserEvent $event)
    {

        $response = new RedirectResponse(
            $this->router->generate('user_account_products')
        );

        $event->setResponse($response);

    }
}