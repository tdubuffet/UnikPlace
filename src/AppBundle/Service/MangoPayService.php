<?php

namespace AppBundle\Service;

use MangoPay;
use UserBundle\Entity\User as UserEntity;

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

    public function createNaturalUser(UserEntity $user)
    {
        $mangoUser = new \MangoPay\UserNatural();
        $mangoUser->PersonType          = "NATURAL";
        $mangoUser->FirstName           = $user->getFirstname();
        $mangoUser->LastName            = $user->getLastname();
        $mangoUser->Birthday            = $user->getBirthday()->getTimestamp()+3600; // Fix one day gap
        $mangoUser->Nationality         = $user->getNationality();
        $mangoUser->CountryOfResidence  = $user->getResidentialCountry();
        $mangoUser->Email               = $user->getEmail();

        return $this->mangoPayApi->Users->Create($mangoUser);
    }

    public function createLegalUser(UserEntity $user)
    {
        $mangoUser = new \MangoPay\UserLegal();
        $mangoUser->Name                                    = $user->getCompanyName();
        $mangoUser->LegalPersonType                         = "BUSINESS";
        $mangoUser->LegalRepresentativeFirstName            = $user->getFirstname();
        $mangoUser->LegalRepresentativeLastName             = $user->getLastname();
        $mangoUser->LegalRepresentativeBirthday             = $user->getBirthday()->getTimestamp()+3600; // Fix one day gap
        $mangoUser->LegalRepresentativeNationality          = $user->getNationality();
        $mangoUser->LegalRepresentativeCountryOfResidence   = $user->getResidentialCountry();
        $mangoUser->Email                                   = $user->getEmail();

        return $this->mangoPayApi->Users->Create($mangoUser);
    }

    public function createWallets($mangoPayUserId)
    {
        $blockedWallet = new \MangoPay\Wallet();
        $blockedWallet->Tag = "BLOCKED";
        $blockedWallet->Owners = array($mangoPayUserId);
        $blockedWallet->Description = "Blocked wallet";
        $blockedWallet->Currency = "EUR";

        $freeWallet = new \MangoPay\Wallet();
        $freeWallet->Tag = "FREE";
        $freeWallet->Owners = array($mangoPayUserId);
        $freeWallet->Description = "Free wallet";
        $freeWallet->Currency = "EUR";

        $blockedWalletResult    = $this->mangoPayApi->Wallets->Create($blockedWallet);
        $freeWalletResult       = $this->mangoPayApi->Wallets->Create($freeWallet);

        return ['blocked' => $blockedWalletResult, 'free' => $freeWalletResult];
    }

    /**
     * Get transactions on free wallet
     *
     * @param $walletId
     * @param int $currentPage
     * @param int $itemsPerPage
     * @return mixed
     */
    public function getFreeWalletTransactions($walletId, &$pagination)
    {

        $sorting = new \MangoPay\Sorting();
        $sorting->AddField('CreationDate', \MangoPay\SortDirection::DESC);

        return $this->mangoPayApi->Wallets->GetTransactions($walletId, $pagination, null, $sorting);
    }

    /**
     * Get wallet by Id
     * @param int $walletId Wallet identifier
     * @return \MangoPay\Wallet
     */
    public function getWalletId($walletId)
    {

        return $this->mangoPayApi->Wallets->Get($walletId);
    }

}