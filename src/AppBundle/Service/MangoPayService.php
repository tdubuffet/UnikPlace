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
    }

    public function getMangoPayApi()
    {
        return $this->mangoPayApi;
    }

}