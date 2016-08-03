<?php

namespace UserBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Service\MangoPayService;
use Doctrine\ORM\EntityManager;

class EditListener implements EventSubscriberInterface
{

    private $mangopayService;

    public function __construct(MangoPayService $mangopayService)
    {
        $this->mangopayService = $mangopayService;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::PROFILE_EDIT_SUCCESS => 'onEditSucess'
        );
    }

    public function onEditSucess(FormEvent $event)
    {
        $form = $event->getForm();

        $data = $form->getData();


        $this->mangopayService->updateUser($data);

    }
}