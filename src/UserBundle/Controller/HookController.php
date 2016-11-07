<?php

namespace UserBundle\Controller;

use MangoPay\EventType;
use MangoPay\Hook;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class HookController
 * @package UserBundle\Controller
 */
class HookController extends Controller
{

    /**
     * @Route("/hook/mangopay/kyc", name="hook_mangopay_kyc")
     * @param Request $request
     */
    public function mangopayKycHookAction(Request $request)
    {
        $eventType      = $request->get('EventType', false);
        $ressourceId    = $request->get('RessourceId', false);
        $date           = $request->get('Date', false);



        if (!$eventType || !$ressourceId || !$date) {
            throw new \Exception('No valid hook');
        }

        switch($eventType) {
        case 'KYC_SUCCEEDED':
        case 'KYC_FAILED':

            $kycDocument = $this->get('mangopay_service')->getMangoPayApi()->KycDocuments->Get($ressourceId);

            $type = $kycDocument->Type;

            switch($type) {
            case 'IDENTITY_PROOF':

                $documentName = "Carte d'identité";
                break;
            case 'REGISTRATION_PROOF':

                $documentName = "Extrait KBIS";
                break;

            case 'ARTICLES_OF_ASSOCIATION':

                $documentName = "Mémo de l'entreprise";
                break;

            case 'SHAREHOLDER_DECLARATION':

                $documentName = "Déclaration d'actionnaire";
                break;
            }

            $user = $this->getDoctrine()->getRepository('UserBundle:User')->findOneByIdMangopayUserId($kycDocument->UserId);

            if (!$user) {
                throw new \Exception('No valid user id');
            }

            if ($eventType == 'KYC_SUCCEEDED') {
                $this->get('mailer_sender')->sendKYCValidatedEmailMessage($user, $documentName);

            } elseif ($eventType == 'KYC_FAILED') {
                $this->get('mailer_sender')->sendKYCFailedEmailMessage($user, $documentName);
            }
            break;
        }

        return new Response('Ok');
    }

    /**
     * @Route("/hook/mangopay/create", name="hook_mangopay_create")
     * @param Request $request
     */
    public function hookAction(Request $request)
    {

        $hook = new Hook();
        $hook->Url = $this->generateUrl('hook_mangopay_kyc', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $hook->Validity = 'VALID';
        $hook->Status = 'ENABLED';
        $hook->EventType = EventType::KycSucceeded;

        $this->get('mangopay_service')->getMangoPayApi()->Hooks->Create($hook);

        $hook = new Hook();
        $hook->Url = $this->generateUrl('hook_mangopay_kyc', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $hook->Validity = 'VALID';
        $hook->Status = 'ENABLED';
        $hook->EventType = EventType::KycFailed;

        $this->get('mangopay_service')->getMangoPayApi()->Hooks->Create($hook);

        exit('OK');
    }

    /**
     * @Route("/hook/mangopay/all", name="hook_mangopay_all")
     * @param Request $request
     */
    public function hooksAction(Request $request)
    {
        $returns = $this->get('mangopay_service')->getMangoPayApi()->Hooks->GetAll();

        var_dump($returns); die;
    }

}