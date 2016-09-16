<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class CheckController extends Controller
{

    /**
     * @Route("/check-email", name="check_email", options={"expose"=true})
     * @Method({"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function checkEmailAction(Request $request)
    {
        $result = 'true';
        $email = $request->query->get('email_registration');
        if (!$email) {
            $result = 'Aucune adresse email spécifié.';
        }
        else {
            $user = $this->getDoctrine()->getRepository('UserBundle:User')->findOneByEmail($email);
            if ($user) {
                $result = 'Cette adresse email est déjà associée à un compte.';
            }
        }
        return new JsonResponse($result);
    }

}