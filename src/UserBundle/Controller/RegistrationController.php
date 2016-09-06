<?php

namespace UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Controller\RegistrationController as BaseRegistrationController;

class RegistrationController extends BaseRegistrationController
{

    /**
     * Overriding signup to add custom logic.
     */
    public function registerAction(Request $request)
    {
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return new RedirectResponse($this->container->get('router')->generate('user_account_products'));
        }
        return parent::registerAction($request);
    }
}