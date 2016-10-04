<?php

namespace DeliveryBundle\Service;
use DeliveryBundle\Emc\Quotation;
use ProductBundle\Entity\Product;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 28/09/2016
 * Time: 15:53
 */
class Delivery
{

    private $carriersEnabled;

    public function __construct($parameters)
    {

        if (!isset($parameters['mode']) || !isset($parameters['user']) || !isset($parameters['pass']) || !isset($parameters['key']) || !isset($parameters['carriers'])){

            throw new Exception('Information config EMC not found');

        }

        $this->carriersEnabled = $parameters['carriers'];

        define("EMC_MODE", $parameters['mode']);
        define("EMC_USER", $parameters['user']);
        define("EMC_PASS", $parameters['pass']);
        define("EMC_KEY", $parameters['key']);

    }

    public function enabledCarriers()
    {
        return array(
            0 => 'POFRColissimoAccess',
            1 => 'SOGPRelaisColis',
            2 => 'POFRColissimoAccess',
            3 => 'CHRPChrono13',
            4 => 'UPSEExpressSaver',
            5 => 'DHLEExpressWorldwide'
        );
    }


    /**
     * Find location with IP USER
     *
     * @param $ip
     * @return mixed
     */
    public function findCityZipCodeByIp($ip)
    {

        if ($ip == '::1'){
            $ip =  '109.0.228.228';
        }

        $location = file_get_contents('http://ipinfo.io/'.$ip);

        return json_decode($location);
    }

    /**
     * Find deliveries at EMC for product & User (Or IP)
     *
     * @param User $user
     * @param $ip
     * @param Product $product
     * @return array
     */
    public function findDeliveryByProduct(User $user, $ip, Product $product)
    {

        if ($user && $user->getAddresses()->count() > 0) {

            $addressTo = $user->getAddresses()->first();

            $to = array(
                'pays'          => 'FR', //Bouchon not international
                'ville'         => $addressTo->getCity()->getName(),
                'type'          => 'particulier',
                'adresse'       => $addressTo->getStreet(),
                'code_postal'   => $addressTo->getCity()->getZipcode()
            );

        } else {

            $data = $this->findCityZipCodeByIp($ip);

            if (!isset($data->postal) || !isset($data->postal)) {
                return [];
            }

            $to = array(
                'pays'          => 'FR', //Bouchon not international
                'ville'         => $data->city,
                'type'          => 'particulier',
                'adresse'       => '',
                'code_postal'   => $data->postal
            );
        }


        $from = array(
            'pays'          => 'FR',
            'code_postal'   => $product->getAddress()->getCity()->getZipcode(),
            'ville'         => $product->getAddress()->getCity()->getName(),
            'type'          => 'entreprise',
            'adresse'       => ''
        );


        $additionalParams = array(
            'collecte' => date("Y-m-d"),
            'delay' => 'aucun',
            //'offers' => $this->carriersEnabled
        );

        $parcels = array(
            'type' => 'colis', // your shipment type: "encombrant" (bulky parcel), "colis" (parcel), "palette" (pallet), "pli" (envelope)
            'dimensions' => array(
                1 => array(
                    'poids' => $product->getWeight() / 1000,
                    'longueur' => $product->getLength() * 100,
                    'largeur' => $product->getWidth() * 100,
                    'hauteur' => $product->getHeight() * 100
                )
            )
        );

        $lib = new Quotation();
        $lib->getQuotation($from, $to, $parcels, $additionalParams);


        $this->handlerError($lib);


        return $lib->offers;
    }

    public function handlerError($lib)
    {

        if ($lib->resp_error) {
            $error = '';

            foreach ($lib->resp_errors_list as $m => $message) {
                $error .= $message["message"];
            }

            throw new \Exception($error);

        } elseif ($lib->curl_error) {
            throw new \Exception("Unable to send the request: ".$lib->curl_error_text);
        }

    }

}