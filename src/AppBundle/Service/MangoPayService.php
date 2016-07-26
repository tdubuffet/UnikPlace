<?php

namespace AppBundle\Service;

use MangoPay;
use OrderBundle\Entity\Order;
use UserBundle\Entity\User as UserEntity;
use UserBundle\Entity\User;

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
     * @param int $pagination
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

    /**
     * Create Iban Bank
     *
     * @param $userId MangoUser id
     * @param $data Data for create ibanBank
     * @return \MangoPay\BankAccount
     */
    public function createIbanBank($userId, $data)
    {
        $BankAccount = new \MangoPay\BankAccount();
        $BankAccount->Type = "IBAN";
        $BankAccount->Details = new MangoPay\BankAccountDetailsIBAN();
        $BankAccount->Details->IBAN = $data['iban'];
        $BankAccount->Details->BIC  = $data['bic'];
        $BankAccount->OwnerName     = $data['name'];
        $BankAccount->OwnerAddress                  = new \MangoPay\Address();
        $BankAccount->OwnerAddress->AddressLine1    = $data['address_street'];
        $BankAccount->OwnerAddress->AddressLine2    = 'Address line 2';
        $BankAccount->OwnerAddress->City            = $data['address_city'];
        $BankAccount->OwnerAddress->Country         = $data['address_country'];
        $BankAccount->OwnerAddress->PostalCode      = $data['address_postal_code'];
        $BankAccount->OwnerAddress->Region          = 'Region';



            $response = $this->mangoPayApi->Users->CreateBankAccount($userId, $BankAccount);

        return $response;
    }

    /**
     * Get Last IBAN Bank
     *
     * @param $userId MangoUser id
     * @return \MangoPay\BankAccount
     */
    public function getIbanBank($userId)
    {

        $sorting = new \MangoPay\Sorting();
        $sorting->AddField('CreationDate', "desc");

        $banks =  $this->mangoPayApi->Users->GetBankAccounts($userId, new \MangoPay\Pagination(), $sorting);
        if (count($banks) == 0) {
            return array();
        }


        return $banks[0];
    }

    /**
     * Transfer money on free wallet to bank
     *
     * @param $user
     * @param $freeWallet
     * @param $bankAccount
     * @return mixed
     */
    public function freeWalletToTransferBank($user)
    {

        $bankAccount = $this->getIbanBank($user->mango_user_id);
        $freeWallet  = $user->getFreeWallet();

        $PayOut = new \MangoPay\PayOut();
        $PayOut->AuthorId           = $user->mango_user_id;
        $PayOut->DebitedWalletID    = $freeWallet->Id;
        $PayOut->DebitedFunds       = new \MangoPay\Money();
        $PayOut->DebitedFunds->Currency = "EUR";
        $PayOut->DebitedFunds->Amount = $freeWallet->Balance->Amount;
        $PayOut->Fees               = new \MangoPay\Money();
        $PayOut->Fees->Currency     = "EUR";
        $PayOut->Fees->Amount       = 0;
        $PayOut->PaymentType        = "BANK_WIRE";
        $PayOut->MeanOfPaymentDetails = new \MangoPay\PayOutPaymentDetailsBankWire();
        $PayOut->MeanOfPaymentDetails->BankAccountId = $bankAccount->Id;

        $response =  $this->mangoPayApi->PayOuts->Create($PayOut);

        $this->addTrace(
            $response,
            "[PayOut] MangoPayUserId => " . $user->getMangoPayUser()->Id . " | FreeWalletId => " . $user->getFreeWallet()->Id
        );

        return $response;
    }

    public function validateWalletToTransferBank($walletId)
    {

        $sorting = new \MangoPay\Sorting();
        $sorting->AddField('CreationDate', \MangoPay\SortDirection::DESC);

        $filters = new \MangoPay\FilterTransactions();
        $filters->Status    = 'CREATED';
        $filters->Type      = 'PAYOUT';
        $filters->Nature    = 'REGULAR';

        $pagination = new \MangoPay\Pagination();

        $values = $this->mangoPayApi->Wallets->GetTransactions($walletId, $pagination, $filters, $sorting);

        if (count($values) > 0) {
            return false;
        }

        return true;
    }

    public function checkStatusPreAuth($preauthorizationId)
    {
        $preauthorization = $this->mangoPayApi->CardPreAuthorizations->Get($preauthorizationId);

        return $preauthorization;
    }

    public function createPayIn(User $buyer, Order $order, $totalAmount, $currencyCode = 'EUR')
    {

        $preauthorization = $this->checkStatusPreAuth($order->getMangopayPreauthorizationId());

        // Get buyer blocked wallet
        $wallet = $this->getWalletId($buyer->getMangopayBlockedWalletId());

        if ($preauthorization->PaymentStatus == "WAITING") {

            // Create Pay In
            $payIn = new \MangoPay\PayIn();
            $payIn->CreditedWalletId = $wallet->Id;
            $payIn->AuthorId = $buyer->getMangopayUserId();
            $payIn->DebitedFunds = new \MangoPay\Money();
            $payIn->DebitedFunds->Amount = $totalAmount[1] * 100;
            $payIn->DebitedFunds->Currency = $currencyCode;
            $payIn->Fees = new \MangoPay\Money();
            $payIn->Fees->Amount = 0;
            $payIn->Fees->Currency = 'EUR';
            $payIn->PaymentType = "CARD";

            // Payment type as PREAUTHORIZED
            $payIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsPreAuthorized();
            $payIn->PaymentDetails->PreauthorizationId = $order->getMangopayPreauthorizationId();

            // Execution type as DIRECT
            $payIn->ExecutionDetails = new \MangoPay\PayInExecutionDetailsDirect();

            $createdPayIn = $this->mangoPayApi->PayIns->Create($payIn);
            if ($createdPayIn->Status == 'SUCCEEDED') {
                return $createdPayIn->Id;
            }

        }
        if ($preauthorization->PayInId != null) {
            return $preauthorization->PayInId;
        }


        //@todo log transaction

        return false;

    }

    /**
     * Refound Pay In
     * @param $userId MangoPay User Id
     * @param $PayInId MangoPay PayIn Id
     * @param $amount Amount Integer
     * @return mixed
     */
    public function refundOrder($userMangoPayId, $PayInId, $amount)
    {

        $Refund = new \MangoPay\Refund();
        $Refund->AuthorId       = $userMangoPayId;
        $Refund->DebitedFunds   = new \MangoPay\Money();

        $Refund->DebitedFunds->Currency = "EUR";
        $Refund->DebitedFunds->Amount = $amount*100;
        $Refund->Fees = new \MangoPay\Money();
        $Refund->Fees->Currency = "EUR";
        $Refund->Fees->Amount = 0; // No fee on refund


        $result = $this->mangoPayApi->PayIns->CreateRefund($PayInId, $Refund);


        $this->addTrace($result,
            '[REFUND] Refund order | PayInId => ' . $PayInId
        );


        return $result;

    }

    public function validateOrder(Order $order)
    {

        $Transfer = new \MangoPay\Transfer();
        $Transfer->AuthorId                 = $order->getUser()->getMangopayUserId();
        $Transfer->DebitedFunds             = new \MangoPay\Money();

        $Transfer->DebitedFunds->Currency   = 'EUR';
        $Transfer->DebitedFunds->Amount     = $order->getAmount() * 100;

        $Transfer->Fees = new \MangoPay\Money();
        $Transfer->Fees->Currency       = "EUR";
        $Transfer->Fees->Amount         = ($Transfer->DebitedFunds->Amount * ($this->config['fee_rate']/100) + $this->config['fixed_fee']*100);

        $Transfer->DebitedWalletID      = $order->getUser()->getMangopayBlockedWalletId();
        $Transfer->CreditedWalletId     = $order->getProduct()->getUser()->getMangopayFreeWalletId();


        $result = $this->mangoPayApi->Transfers->Create($Transfer);

        if (isset($result->Status) && $result->Status == "FAILED") {
            throw new \Exception('Erreur ' . $result->ResultCode . ' - Message: ' . $result->ResultMessage);
        }

        return $result;
    }

}