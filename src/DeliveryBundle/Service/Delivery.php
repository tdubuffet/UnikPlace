<?php

namespace DeliveryBundle\Service;
use DeliveryBundle\Emc\OrderStatus;
use DeliveryBundle\Emc\Quotation;
use OrderBundle\Entity\Order;
use ProductBundle\Entity\Product;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraints\DateTime;
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

    private $router;

    public function __construct($parameters, Router $router)
    {

        if (!isset($parameters['mode']) || !isset($parameters['user']) || !isset($parameters['pass']) || !isset($parameters['key']) || !isset($parameters['carriers'])){

            throw new Exception('Information config EMC not found');

        }

        $this->carriersEnabled = $parameters['carriers'];

        $this->router = $router;

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
                'pays'          => 'FR',
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
            'offers' => $this->carriersEnabled
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

        $deliveries = [];

        foreach ($lib->offers as $offer) {
            $deliveries[$offer['operator']['code'] . $offer['service']['code']] = $offer;
        }

        return $deliveries;
    }

    /**
     * Find deliveries at EMC for product & User (Or IP)
     *
     * @param User $user
     * @param $ip
     * @param Product $product
     * @return array
     */
    public function prepareDeliveryByOrder(Order $order)
    {

        $deliveryCode = $order->getDelivery()->getDeliveryMode()->getCode();

        $user = $order->getUser();
        
        if ($user && $user->getAddresses()->count() > 0) {

            $addressTo = $user->getAddresses()->first();

            $to = array(
                'pays'          => 'FR', //Bouchon not international
                'ville'         => $addressTo->getCity()->getName(),
                'type'          => ($user->getPro()) ? 'entreprise' : 'particulier',
                'adresse'       => $addressTo->getStreet(),
                'code_postal'   => $addressTo->getCity()->getZipcode()
            );

        }



        $product = $order->getProduct();

        $from = array(
            'pays'          => 'FR',
            'code_postal'   => $product->getAddress()->getCity()->getZipcode(),
            'ville'         => $product->getAddress()->getCity()->getName(),
            'type'          => ($order->getProduct()->getUser()->getPro()) ? 'entreprise' : 'particulier',
            'adresse'       => $product->getAddress()->getStreet()
        );


        $additionalParams = array(
            'collecte' => date("Y-m-d"),
            'delay' => 'aucun',
            'offers' => $this->carriersEnabled,

            'assurance.selection' => false,
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

        foreach ($lib->offers as $offer) {
            if ($deliveryCode == $offer['operator']['code'] . $offer['service']['code']) {
                return $offer;
            }
        }

        return false;
    }

    public function validateDate($date)
    {
        $d = \DateTime::createFromFormat('d/m/Y', $date);
        return $d && $d->format('d/m/Y') === $date;
    }

    public function makeOrder(Order $order, $emcValues)
    {
        $delivery = $this->prepareDeliveryByOrder($order);


        $from = array(
            'pays'          => 'FR',  // must be an ISO code, set get_country example on how to get codes
            'code_postal'   => $order->getProduct()->getAddress()->getCity()->getZipcode(),
            'ville'         => $order->getProduct()->getAddress()->getCity()->getName(),
            'type'          => ($order->getProduct()->getUser()->getPro()) ? 'entreprise' : 'particulier', // accepted values are "particulier" or "entreprise"
            'adresse'       => $order->getProduct()->getAddress()->getStreet(),
            'civilite'      => ($order->getProduct()->getAddress()->getCivility() == 'mr') ? 'M': 'Mme',
            'prenom'        => $order->getProduct()->getUser()->getLastname(),
            'nom'           => $order->getProduct()->getUser()->getFirstname(),
            'email'         => 'dubuffet.thibault@gmail.com', //@todo bouchon
            'tel'           => $order->getProduct()->getUser()->getPhone(),
            'infos'         => $order->getProduct()->getAddress()->getAdditional()
        );

        if ($order->getProduct()->getUser()->getPro()) {
            $from['societe']  = $order->getProduct()->getUser()->getCompanyName();
        }

        $to = array(
            'pays'          => 'FR',  // must be an ISO code, set get_country example on how to get codes @todo INTERNATIONAL
            'code_postal'   => $order->getDeliveryAddress()->getCity()->getZipcode(),
            'ville'         =>  $order->getDeliveryAddress()->getCity()->getName(),
            'type'          => 'particulier', // accepted values are "particulier" or "entreprise"
            'adresse'       => $order->getDeliveryAddress()->getStreet(),
            'civilite'      => ($order->getDeliveryAddress()->getCivility() == 'mr') ? 'M' : 'Mme',
            'prenom'        => $order->getUser()->getLastname(),
            'nom'           => $order->getUser()->getFirstname(),
            'email'         => 'dubuffet.thibault@gmail.com', //@todo bouchon
            'tel'           => $order->getUser()->getPhone(),
            'infos'         => $order->getDeliveryAddress()->getAdditional()
        );

        $parcels = array(
            'type' => 'colis', // your shipment type: "encombrant" (bulky parcel), "colis" (parcel), "palette" (pallet), "pli" (envelope)
            'dimensions' => array(
                1 => array(
                    'poids'     => $order->getProduct()->getWeight() / 1000,
                    'longueur'  => $order->getProduct()->getLength() * 100,
                    'largeur'   => $order->getProduct()->getWidth() * 100,
                    'hauteur'   => $order->getProduct()->getHeight() * 100
                )
            )
        );

        $paramsAdds = [
            'collecte'              => date('Y-m-d'),
            'delai'                 => "aucun",
            'assurance.selection'   => false,
            'content_code'          => $order->getId(),
            'valeur'                => $order->getProductAmount(),
            'operator'              => $delivery['operator']['code'],
            'service'               => $delivery['service']['code'],
            'raison'                => 'sale',
            'content_code'          => 40110,
            'url_push'              => $this->router->generate('emc_tracking', [
                'order' => $order->getId(),
                'key' => md5('emc_delivery'),
                UrlGeneratorInterface::ABSOLUTE_URL
            ])//@todo fix content code delivery
        ];

        if (isset($emcValues['date-order']) && $this->validateDate($emcValues['date-order'])) {

            $d = \DateTime::createFromFormat('d/m/Y', $emcValues['date-order']);

            $paramsAdds['collecte'] = $d->format('Y-m-d');

        }

        foreach ($delivery['mandatory'] as $key => $manda) {


            switch($key) {

                case 'colis.description':
                    $paramsAdds['colis.description'] = $order->getProduct()->getName();
                    break;

                case 'disponibilite.HDE':
                    $paramsAdds['disponibilite.HDE'] = $emcValues['disponibilite.HDE'];
                    break;

                case 'disponibilite.HLE':
                    $paramsAdds['disponibilite.HLE'] = $emcValues['disponibilite.HLE'];
                    break;


            }

        }

        $lib = new Quotation();
        $lib->makeOrder($from, $to, $parcels, $paramsAdds, true);

        $this->handlerError($lib);

        return $lib->order;
    }

    public function orderStatus(Order $order)
    {

        $lib = new OrderStatus();

        $lib->getOrderInformations($order->getEmcRef());


        $this->handlerError($lib);

        return $lib->order_info;

    }

    public function handlerError($lib)
    {

        if ($lib->resp_error) {
            $error = '';

            foreach ($lib->resp_errors_list as $m => $message) {
                $error .= $message["message"];
            }

            var_dump($error);

            throw new \Exception($error);

        } elseif ($lib->curl_error) {
            throw new \Exception("Unable to send the request: ".$lib->curl_error_text);
        }

    }

}