<?php

namespace AppBundle\Service;

use MangoPay;


class MangoPayService
{
    private $mangoPayApi;
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
        $this->mangoPayApi = new MangoPay\MangoPayApi();
        $this->mangoPayApi->Config->ClientId = $config['client_id'];
        $this->mangoPayApi->Config->ClientPassword = $config['client_password'];
        $this->mangoPayApi->Config->BaseUrl = $config['base_url'];
        $this->mangoPayApi->Config->TemporaryFolder = $config['temporary_folder'];

        // Create temporary folder if it does not exist
        if (!file_exists($config['temporary_folder'])) {
            mkdir($config['temporary_folder'], 0777, true);
        }
    }

    public function getMangoPayApi()
    {
        return $this->mangoPayApi;
    }

    public function getMangoPayUser($mangoPayUserId)
    {
        return $this->mangoPayApi->Users->Get($mangoPayUserId);
    }

    public function createCardRegistration($mangoPayUserId, $currency)
    {
        $cardRegister = new \MangoPay\CardRegistration();
        $cardRegister->UserId = $mangoPayUserId;
        $cardRegister->Currency = $currency;
        $createdCardRegister = $this->mangoPayApi->CardRegistrations->Create($cardRegister);
        return $createdCardRegister;
    }

    public function getCardRegistration($cardRegistrationId)
    {
        return $this->mangoPayApi->CardRegistrations->Get($cardRegistrationId);
    }

    public function updateCardRegistration($cardRegistration)
    {
        return $this->mangoPayApi->CardRegistrations->Update($cardRegistration);
    }

    public function getCard($cardId)
    {
        return $this->mangoPayApi->Cards->Get($cardId);
    }

    public function createCardPreAuthorization($mangoPayUserId, $amount, $currency, $card, $returnUrl)
    {
        $preAuth = new \MangoPay\CardPreAuthorization();
        $preAuth->AuthorId = $mangoPayUserId;
        $preAuth->DebitedFunds = new \MangoPay\Money();
        $preAuth->DebitedFunds->Amount = $amount * 100;
        $preAuth->DebitedFunds->Currency = $currency;
        $preAuth->SecureMode = 'FORCE';
        $preAuth->CardId = $card->Id;
        $preAuth->SecureModeReturnURL = $returnUrl;
        return $this->mangoPayApi->CardPreAuthorizations->Create($preAuth);
    }

    public function getCardPreAuthorization($preAuthorizationId)
    {
        return $this->mangoPayApi->CardPreAuthorizations->Get($preAuthorizationId);
    }
}