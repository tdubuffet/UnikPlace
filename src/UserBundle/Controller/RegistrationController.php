<?php

namespace UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;

class RegistrationController extends BaseRegistrationController
{

    public function registerAction(Request $request)
    {
        $response = parent::registerAction($request);

        $this->get('session')->getFlashBag()->clear();

        return $response;
    }

}