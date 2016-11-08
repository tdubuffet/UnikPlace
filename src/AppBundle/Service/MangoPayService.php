<?php

namespace AppBundle\Service;

use MangoPay;
use OrderBundle\Entity\Order;
use OrderBundle\Entity\TransactionPayRefund;
use OrderBundle\Entity\TransactionPayTransfert;
use UserBundle\Entity\User as UserEntity;
use UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;

class MangoPayService
{
    private $mangoPayApi;
    private $config;
    private $em;

    public function __construct($config, EntityManager $entityManager)
    {
        $this->config = $config;
        $this->mangoPayApi = new MangoPay\MangoPayApi();
        $this->mangoPayApi->Config->ClientId = $config['client_id'];
        $this->mangoPayApi->Config->ClientPassword = $config['client_password'];
        $this->mangoPayApi->Config->BaseUrl = $config['base_url'];
        $this->mangoPayApi->Config->TemporaryFolder = $config['temporary_folder'];

        $this->em = $entityManager;

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

    public function updateUser(UserEntity $user)
    {

        $mangoUser = $this->getMangoPayUser($user->getMangopayUserId());

        if ($user->getPro()) {

            $mangoUser->Name                                    = $user->getCompanyName();
            $mangoUser->LegalRepresentativeFirstName            = $user->getFirstname();
            $mangoUser->LegalRepresentativeLastName             = $user->getLastname();
            $mangoUser->LegalRepresentativeBirthday             = $user->getBirthday()->getTimestamp()+3600; // Fix one day gap
            $mangoUser->LegalRepresentativeNationality          = $user->getNationality();
            $mangoUser->LegalRepresentativeCountryOfResidence   = $user->getResidentialCountry();
            $mangoUser->Email                                   = $user->getEmail();

            unset($mangoUser->HeadquartersAddress);
            unset($mangoUser->LegalRepresentativeAddress);

        } else {

            $mangoUser->FirstName           = $user->getFirstname();
            $mangoUser->LastName            = $user->getLastname();
            $mangoUser->Birthday            = $user->getBirthday()->getTimestamp()+3600; // Fix one day gap
            $mangoUser->Nationality         = $user->getNationality();
            $mangoUser->CountryOfResidence  = $user->getResidentialCountry();
            $mangoUser->Email               = $user->getEmail();

            unset($mangoUser->Address);

        }

        return $this->mangoPayApi->Users->Update($mangoUser);
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
        $pagination = new \MangoPay\Pagination();

        $banks =  $this->mangoPayApi->Users->GetBankAccounts($userId, $pagination, $sorting);
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

        $bankAccount = $this->getIbanBank($user->getMangopayUserId());
        $freeWallet  = $this->getWalletId($user->getMangopayFreeWalletId());


        $PayOut = new \MangoPay\PayOut();
        $PayOut->AuthorId           = $user->getMangopayUserId();
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

        return false;

    }

    /**
     * Refound Pay In
     * @param $userId MangoPay\User Id
     * @param $PayInId MangoPay\PayIn|string Id
     * @param $amount int Integer
     * @return mixed
     */
    public function refundOrder($userMangoPayId, $PayInId, Order $order)
    {

        $Refund = new \MangoPay\Refund();
        $Refund->AuthorId       = $userMangoPayId;
        $Refund->DebitedFunds   = new \MangoPay\Money();

        $Refund->DebitedFunds->Currency = "EUR";
        $Refund->DebitedFunds->Amount = $order->getAmount()*100;
        $Refund->Fees = new \MangoPay\Money();
        $Refund->Fees->Currency = "EUR";
        $Refund->Fees->Amount = 0; // No fee on refund


        $result = $this->mangoPayApi->PayIns->CreateRefund($PayInId, $Refund);


        $transactionPayRefund = new TransactionPayRefund();
        $transactionPayRefund->setOrder($order);
        $transactionPayRefund->setAmount($order->getAmount());
        $transactionPayRefund->setType('all');
        $transactionPayRefund->setDate(new \DateTime());
        $this->em->persist($transactionPayRefund);
        $this->em->flush();

        return $result;

    }

    /**
     * Refound Pay In
     * @param $userId MangoPay\User Id
     * @param $PayInId MangoPay\PayIn|string Id
     * @param $amount int Integer
     * @return mixed
     */
    public function refundOrderByType(Order $order, $type)
    {

        $PayInId            = $order->getMangopayPayinId();
        $userMangoPayId     = $order->getUser()->getMangopayUserId();

        if (empty($PayInId)) {
            throw new \Exception('Aucun payment sur cette commande');
        }

        if ($type == 'all') {

            $refund = $this->em->getRepository('OrderBundle:TransactionPayRefund')->findOneBy([
                'order' => $order,
                'type' => 'all'
            ]);

            if ($refund) {
                throw new \Exception('Un remboursement total pour cette commande existe déja.');
            }

            return $this->refundOrder($userMangoPayId, $PayInId, $order);

        } elseif ($type == 'delivery') {
            $refund = $this->em->getRepository('OrderBundle:TransactionPayRefund')->findOneBy([
                'order' => $order,
                'type' => 'delivery'
            ]);

            if ($refund) {
                throw new \Exception('Vous avez déja remboursé la livraison');
            }

            $amount = $order->getDeliveryAmount();
        } elseif ($type == 'product') {
            $refunds = $this->em->getRepository('OrderBundle:TransactionPayRefund')->findBy([
                'order' => $order,
                'type' => 'product'
            ]);

            $transaction = $this->em->getRepository('OrderBundle:Transaction')->findOneBy([
                'order' => $order
            ]);

            $totalRefund = 0;

            foreach($refunds as $r) {
                $totalRefund += $r->getAmount();
            }

            if ($totalRefund >= $order->getProductAmount()) {
                throw new \Exception('Vous avez déja remboursé la totalité des produits.');
            }

            $amount = $transaction->getProductPrice();
        } else {
            throw new \Exception('Not found code');
        }

        $Refund = new \MangoPay\Refund();
        $Refund->AuthorId       = $userMangoPayId;
        $Refund->DebitedFunds   = new \MangoPay\Money();

        $Refund->DebitedFunds->Currency = "EUR";
        $Refund->DebitedFunds->Amount = $amount*100;
        $Refund->Fees = new \MangoPay\Money();
        $Refund->Fees->Currency = "EUR";
        $Refund->Fees->Amount = 0; // No fee on refund


        $result = $this->mangoPayApi->PayIns->CreateRefund($PayInId, $Refund);


        $transactionPayRefund = new TransactionPayRefund();
        $transactionPayRefund->setOrder($order);
        $transactionPayRefund->setAmount($amount);
        $transactionPayRefund->setType($type);
        $transactionPayRefund->setDate(new \DateTime());
        $this->em->persist($transactionPayRefund);

        $this->em->flush();
    }

    public function validateOrder(Order $order, &$feeRate)
    {

        $transaction = $this->em->getRepository('OrderBundle:Transaction')->findOneBy([
            'order' => $order
        ]);

        if (!$transaction) {
            throw new \Exception('Not transaction found');
        }

        $productAmount = $transaction->getTotalProductPrice();
        $transactionRefundProducts = $this->em->getRepository('OrderBundle:TransactionPayRefund')->findBy([
            'order' => $order,
            'type' => 'product'
        ]);
        foreach($transactionRefundProducts as $refund) {
            $productAmount -= $refund->getAmount();
        }

        $feeRate = $this->getFeeRateFromProductAndOrderAmount(
            $order->getProduct(), $order->getProductAmount()
        );


        $deliveryAmount = $order->getDeliveryAmount();
        $transactionRefundDelivery= $this->em->getRepository('OrderBundle:TransactionPayRefund')->findOneBy([
            'order' => $order,
            'type' => 'delivery'
        ]);

        if ($transactionRefundDelivery) {
            $deliveryAmount -= $transactionRefundDelivery->getAmount();
        }
        $debitedSupplEmc = 0;
        if ($order->getEmc()) {
            $debitedSupplEmc = $deliveryAmount;
        }

        $totalAmount = $deliveryAmount + $productAmount;

        $Transfer = new \MangoPay\Transfer();
        $Transfer->AuthorId                 = $order->getUser()->getMangopayUserId();
        $Transfer->DebitedFunds             = new \MangoPay\Money();

        $Transfer->DebitedFunds->Currency   = 'EUR';
        $Transfer->DebitedFunds->Amount     = $totalAmount * 100;

        $Transfer->Fees = new \MangoPay\Money();
        $Transfer->Fees->Currency       = "EUR";
        $Transfer->Fees->Amount         = (($productAmount *100) * ($feeRate/100) + $this->config['fixed_fee']*100 + $debitedSupplEmc*100);

        $Transfer->DebitedWalletID      = $order->getUser()->getMangopayBlockedWalletId();
        $Transfer->CreditedWalletId     = $order->getProduct()->getUser()->getMangopayFreeWalletId();

        $transactionPayTransfert = new TransactionPayTransfert();
        $transactionPayTransfert->setDate(new \DateTime());
        $transactionPayTransfert->setOrder($order);
        $transactionPayTransfert->setFees($productAmount* ($feeRate/100) + $this->config['fixed_fee']);
        $transactionPayTransfert->setAmount($totalAmount - $debitedSupplEmc);
        $transactionPayTransfert->setAmountWithoutFees($totalAmount - ($productAmount * ($feeRate/100) + $this->config['fixed_fee'] + $debitedSupplEmc));


        $result = $this->mangoPayApi->Transfers->Create($Transfer);

        if (isset($result->Status) && $result->Status == "FAILED") {
            throw new \Exception('Erreur ' . $result->ResultCode . ' - Message: ' . $result->ResultMessage);
        }

        $this->em->persist($transactionPayTransfert);
        $this->em->flush();

        return $result;
    }

    public function isKYCValidBuyer(UserEntity $user, $inputAmount = 0)
    {
        $input  = $inputAmount;

        /** User is regular, KYC is validate */
        if ($this->getMangoPayUser($user->getMangopayUserId())->KYCLevel == "REGULAR"){
            return 0;
        }
        // As buyer
        $orders = $user->getOrders();
        foreach($orders as $order) {
            $input += $order->getAmount();
        }

        return $input;
    }


    public function isKYCValidUser(UserEntity $user, $inputAmount = 0, $outputAmount = 0)
    {
        $input  = $inputAmount;
        $output  = $outputAmount;

        /** User is regular, KYC is validate */
        if ($this->getMangoPayUser($user->getMangopayUserId())->KYCLevel == "REGULAR"){
            return true;
        }

        // As buyer
        $orders = $user->getOrders();
        foreach($orders as $order) {
            $input += $order->getAmount();
        }

        // As seller
        $products = $user->getProducts();
        foreach ($products as $product) {
            foreach ($product->getOrders() as $order) {
                $output += $order->getAmount();
            }
        }

        // Check
        if ($input >= $this->config['max_input'] || $output >= $this->config['max_output']) {
            return false;
        }
        return true;
    }


    public function sendKYCRegularNaturalUser(MangoPay\UserNatural $mangoUser, $data)
    {
        $KycDocument = new \MangoPay\KycDocument();
        $KycDocument->Type = "IDENTITY_PROOF";
        $result = $this->mangoPayApi->Users->CreateKycDocument($mangoUser->Id, $KycDocument);

        $doneKyc = $this->mangoPayApi->Users->CreateKycPageFromFile($mangoUser->Id, $result->Id, $data['identity_file']->getPathName());

        $KycDocument = new \MangoPay\KycDocument();
        $KycDocument->Id = $result->Id;
        $KycDocument->Status = "VALIDATION_ASKED";

        $this->mangoPayApi->Users->UpdateKycDocument($mangoUser->Id, $KycDocument);

        $mangoUser->Occupation      = $data['occupation'];
        $mangoUser->IncomeRange     = $data['income_range'];

        $Address = new \MangoPay\Address();
        $Address->AddressLine1   = $data['address_street'];
        $Address->PostalCode     = $data['address_postal_code'];
        $Address->City           = $data['address_city'];
        $Address->Country        = $data['address_country'];

        $mangoUser->Address         = $Address;

        return $this->mangoPayApi->Users->Update($mangoUser);
    }

    public function sendKYCRegularLegalUser(MangoPay\UserLegal $mangoUser, $data)
    {
        $cardIdentity = $data['card_identity']->getPathName();

        /** @var  cardIdentity */
        $KycDocument = new \MangoPay\KycDocument();
        $KycDocument->Type = "IDENTITY_PROOF";
        $result = $this->mangoPayApi->Users->CreateKycDocument($mangoUser->Id, $KycDocument);
        $doneKyc = $this->mangoPayApi->Users->CreateKycPageFromFile($mangoUser->Id, $result->Id, $cardIdentity);
        $KycDocument = new \MangoPay\KycDocument();
        $KycDocument->Id = $result->Id;
        $KycDocument->Status = "VALIDATION_ASKED";
        $this->mangoPayApi->Users->UpdateKycDocument($mangoUser->Id, $KycDocument);




        $proofRegistration = $data['proof_registration']->getPathName();

        /** @var  REGISTRATION_PROOF */
        $KycDocument = new \MangoPay\KycDocument();
        $KycDocument->Type = "REGISTRATION_PROOF";
        $result = $this->mangoPayApi->Users->CreateKycDocument($mangoUser->Id, $KycDocument);
        $doneKyc = $this->mangoPayApi->Users->CreateKycPageFromFile($mangoUser->Id, $result->Id, $proofRegistration);
        $KycDocument = new \MangoPay\KycDocument();
        $KycDocument->Id = $result->Id;
        $KycDocument->Status = "VALIDATION_ASKED";
        $this->mangoPayApi->Users->UpdateKycDocument($mangoUser->Id, $KycDocument);


        $certifiedArticles = $data['certified_articles']->getPathName();

        /** @var  REGISTRATION_PROOF */
        $KycDocument = new \MangoPay\KycDocument();
        $KycDocument->Type = "ARTICLES_OF_ASSOCIATION";
        $result = $this->mangoPayApi->Users->CreateKycDocument($mangoUser->Id, $KycDocument);
        $doneKyc = $this->mangoPayApi->Users->CreateKycPageFromFile($mangoUser->Id, $result->Id, $certifiedArticles);
        $KycDocument = new \MangoPay\KycDocument();
        $KycDocument->Id = $result->Id;
        $KycDocument->Status = "VALIDATION_ASKED";
        $this->mangoPayApi->Users->UpdateKycDocument($mangoUser->Id, $KycDocument);

        $shareholderDeclaration = $data['shareholder_declaration']->getPathName();

        /**
         @var  SHAREHOLDER_DECLARATION */
        $KycDocument = new \MangoPay\KycDocument();
        $KycDocument->Type = "SHAREHOLDER_DECLARATION";
        $result = $this->mangoPayApi->Users->CreateKycDocument($mangoUser->Id, $KycDocument);
        $doneKyc = $this->mangoPayApi->Users->CreateKycPageFromFile($mangoUser->Id, $result->Id, $shareholderDeclaration);
        $KycDocument = new \MangoPay\KycDocument();
        $KycDocument->Id = $result->Id;
        $KycDocument->Status = "VALIDATION_ASKED";
        $response = $this->mangoPayApi->Users->UpdateKycDocument($mangoUser->Id, $KycDocument);


        $headquarterAddress = new \MangoPay\Address();
        $headquarterAddress->AddressLine1   = $data['headquarter_address_street'];
        $headquarterAddress->PostalCode     = $data['headquarter_address_postal_code'];
        $headquarterAddress->City           = $data['headquarter_address_city'];
        $headquarterAddress->Country        = $data['headquarter_address_country'];


        $legalRepresentativeAddress = new \MangoPay\Address();
        $legalRepresentativeAddress->AddressLine1   = $data['legal_representative_address_street'];
        $legalRepresentativeAddress->PostalCode     = $data['legal_representative_address_postal_code'];
        $legalRepresentativeAddress->City           = $data['legal_representative_address_city'];
        $legalRepresentativeAddress->Country        = $data['legal_representative_address_country'];

        $mangoUser->HeadquartersAddress         = $headquarterAddress;
        $mangoUser->LegalRepresentativeAddress  = $legalRepresentativeAddress;

        return $this->mangoPayApi->Users->Update($mangoUser);
    }

    public function getListDocumentsByUserId($userId)
    {
        return $this->mangoPayApi->Users->GetKycDocuments($userId);
    }

    public function getFeeRateFromProductAndOrderAmount($product, $price)
    {
        $result = null;
        $seller = $product->getUser();
        $feeRateType = $seller->getPro() == 1 ? 'pro' : 'individual';
        $feeRates = $this->em->getRepository('OrderBundle:FeeRate')->findBy(['type' => $feeRateType], ['minimum' => 'ASC']);
        $feeRatesArray = [];
        foreach ($feeRates as $feeRate) {
            $feeRatesArray[] = ['rate' => $feeRate->getRate(), 'min' => $feeRate->getMinimum()];
        }
        $feeRates = $feeRatesArray;
        foreach ($feeRates as $idx => $feeRate) {
            if (isset($feeRates[$idx +1]) && $price < $feeRates[$idx +1]['min'] && $result == null) {
                $result = $feeRate['rate'];
            }
            else if (!isset($feeRates[$idx +1]) && $result == null) {
                $result = $feeRate['rate'];
            }
        }
        if (is_null($result)) {
            throw new \Exception('Cannot find fee rate for this price.');
        }
        return $result;
    }
}