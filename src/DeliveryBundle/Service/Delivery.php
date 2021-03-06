<?php

namespace DeliveryBundle\Service;
use DeliveryBundle\Emc\ContentCategory;
use DeliveryBundle\Emc\OrderStatus;
use DeliveryBundle\Emc\Quotation;
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;
use LocationBundle\Entity\Address;
use OrderBundle\Entity\Order;
use ProductBundle\Entity\Product;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
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

    private $em;

    public function __construct($parameters, Router $router, EntityManager $em)
    {

        if (!isset($parameters['mode']) || !isset($parameters['user']) || !isset($parameters['pass']) || !isset($parameters['key']) || !isset($parameters['carriers'])){

            throw new Exception('Information config EMC not found');

        }

        $this->carriersEnabled = $parameters['carriers'];

        $this->router = $router;

        $this->em = $em;

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
    public function findDeliveryByProduct(User $user, Address $address, $ip, Product $product)
    {

        $session = new Session();
        $quantity = $session->get('cart_quantity', []);

        $to = array(
            'pays'          => $address->getCountry()->getCode(),
            'ville'         => $address->getCity()->getName(),
            'type'          => ($user->getPro()) ? 'entreprise' : 'particulier',
            'adresse'       => $address->getStreet(),
            'code_postal'   => $address->getCity()->getZipcode()
        );

        $from = array(
            'pays'          => $product->getAddress()->getCountry()->getCode(),
            'code_postal'   => $product->getAddress()->getCity()->getZipcode(),
            'ville'         => $product->getAddress()->getCity()->getName(),
            'type'          => ($product->getUser()->getPro()) ? 'entreprise' : 'particulier',
            'adresse'       => $product->getAddress()->getStreet()
        );

        $quantity = (isset($quantity[$product->getId()])) ? $quantity[$product->getId()] : 1;
        $additionalParams = array(
            'collecte' => date("Y-m-d"),
            'delay' => 'aucun',
            //'offers' => $this->carriersEnabled,
            'content_code'          => 60122,
            'valeur'                => $product->getPrice() * $quantity,
        );


        $type = 'colis';
        $weight = $product->getWeight() / 1000;
        if ($weight >= 70) {
            $type = 'encombrant';
        }

        $parcels = array(
            'type' => $type,
            // your shipment type: "encombrant" (bulky parcel), "colis" (parcel), "palette" (pallet), "pli" (envelope)
            'dimensions' => []
        );


        for($i = 0; $i < $quantity; $i++) {
            $parcels['dimensions'][] = [
                'poids' => $product->getWeight() / 1000,
                'longueur' => $product->getParcelLength(),
                'largeur' => $product->getParcelWidth(),
                'hauteur' => $product->getParcelHeight(),
            ];
        }


        $lib = new Quotation();
        $lib->getQuotation($from, $to, $parcels, $additionalParams);


        $this->handlerError($lib);

        $deliveries = [];


        foreach ($lib->offers as $offer) {
            if ($offer['collection']['type'] !=  'DROPOFF_POINT' && $offer['delivery']['type'] != 'PICKUP_POINT') {
                $deliveries[$offer['operator']['code'] . $offer['service']['code']] = $offer;
            }
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

        $addressTo = $order->getDeliveryAddress();

        $to = array(
            'pays'          => $addressTo->getCountry()->getCode(),
            'ville'         => $addressTo->getCity()->getName(),
            'type'          => ($user->getPro()) ? 'entreprise' : 'particulier',
            'adresse'       => $addressTo->getStreet(),
            'code_postal'   => $addressTo->getCity()->getZipcode()
        );


        $product = $order->getProduct();

        $from = array(
            'pays'          => $product->getAddress()->getCountry()->getCode(),
            'code_postal'   => $product->getAddress()->getCity()->getZipcode(),
            'ville'         => $product->getAddress()->getCity()->getName(),
            'type'          => ($order->getProduct()->getUser()->getPro()) ? 'entreprise' : 'particulier',
            'adresse'       => $product->getAddress()->getStreet()
        );


        $date = new \DateTime();
        $date->modify('+1 day');

        $additionalParams = array(
            'collecte' => $date->format("Y-m-d"),
            'delay' => 'aucun',
            //'offers' => $this->carriersEnabled,
            'content_code' => 60122,
            'assurance.selection' => false,
            'valeur'                => $order->getProductAmount(),
        );

        $type = 'colis';
        $weight = $product->getWeight() / 1000;
        if ($weight >= 70) {
            $type = 'encombrant';
        }

        $parcels = array(
            'type' => 'colis', // your shipment type: "encombrant" (bulky parcel), "colis" (parcel), "palette" (pallet), "pli" (envelope)
            'dimensions' => []
        );

        $quantity = $order->getQuantity();
        for($i = 0; $i < $quantity; $i++) {
            $parcels['dimensions'][] = [
                'poids' => $product->getWeight() / 1000,
                'longueur' => $product->getParcelLength(),
                'largeur' => $product->getParcelWidth(),
                'hauteur' => $product->getParcelHeight(),
            ];
        }

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

    public function makeOrder(Order &$order, $emcValues)
    {
        $delivery = $this->prepareDeliveryByOrder($order);


        $from = array(
            'pays'          => $order->getProduct()->getAddress()->getCountry()->getCode(),
            'code_postal'   => $order->getProduct()->getAddress()->getCity()->getZipcode(),
            'ville'         => $order->getProduct()->getAddress()->getCity()->getName(),
            'type'          => ($order->getProduct()->getUser()->getPro()) ? 'entreprise' : 'particulier', // accepted values are "particulier" or "entreprise"
            'adresse'       => $order->getProduct()->getAddress()->getStreet(),
            'civilite'      => ($order->getProduct()->getAddress()->getCivility() == 'mr') ? 'M': 'Mme',
            'prenom'        => $order->getProduct()->getUser()->getLastname(),
            'nom'           => $order->getProduct()->getUser()->getFirstname(),
            'email'         => $order->getProduct()->getUser()->getEmail(),
            'tel'           => $order->getProduct()->getUser()->getPhone(),
            'infos'         => $order->getProduct()->getAddress()->getAdditional()
        );

        if ($order->getProduct()->getUser()->getPro()) {
            $from['societe']  = $order->getProduct()->getUser()->getCompanyName();
        }

        $to = array(
            'pays'          => $order->getDeliveryAddress()->getCountry()->getCode(),
            'code_postal'   => $order->getDeliveryAddress()->getCity()->getZipcode(),
            'ville'         =>  $order->getDeliveryAddress()->getCity()->getName(),
            'type'          => ($order->getUser()->getPro()) ? 'entreprise' : 'particulier',
            'adresse'       => $order->getDeliveryAddress()->getStreet(),
            'civilite'      => ($order->getDeliveryAddress()->getCivility() == 'mr') ? 'M' : 'Mme',
            'prenom'        => $order->getUser()->getLastname(),
            'nom'           => $order->getUser()->getFirstname(),
            'email'         => $order->getUser()->getEmail(),
            'tel'           => $order->getUser()->getPhone(),
            'infos'         => $order->getDeliveryAddress()->getAdditional()
        );

        $type = 'colis';
        $weight = $order->getProduct()->getWeight() / 1000;
        if ($weight >= 70) {
            $type = 'encombrant';
        }
        $parcels = array(
            'type' => 'colis', // your shipment type: "encombrant" (bulky parcel), "colis" (parcel), "palette" (pallet), "pli" (envelope)
            'dimensions' => []
        );

        $quantity = $order->getQuantity();
        for($i = 0; $i < $quantity; $i++) {
            $parcels['dimensions'][] = [
                'poids' => $order->getProduct()->getWeight() / 1000,
                'longueur' => $order->getProduct()->getParcelLength(),
                'largeur' => $order->getProduct()->getParcelWidth(),
                'hauteur' => $order->getProduct()->getParcelHeight(),
            ];
        }

        $paramsAdds = [
            'collecte'              => date('Y-m-d'),
            'delai'                 => "aucun",
            'assurance.selection'   => false,
            'valeur'                => $order->getProductAmount(),
            'operator'              => $delivery['operator']['code'],
            'service'               => $delivery['service']['code'],
            'raison'                => 'sale',
            'content_code'          => 60122,
            'url_push'              => $this->router->generate('emc_tracking', [
                'order' => $order->getId(),
                'key' => md5('emc_delivery')
            ], UrlGeneratorInterface::ABSOLUTE_URL)
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

                case 'depot.pointrelais':
                    $paramsAdds['depot.pointrelais'] = 'POFR-POST';
                    break;

                case 'type_emballage.emballage':
                    $paramsAdds['type_emballage.emballage'] = '1';
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

    public function downloadBordereau($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . base64_encode(EMC_USER . ':' . EMC_PASS) . ''
        ]);

        curl_setopt($ch, CURLOPT_CAINFO , dirname(__FILE__).'/../Emc/ca/ca-bundle.crt');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $result = curl_exec($ch);

        // We now display the pdf
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="document.pdf"');
        echo $result;
        die();
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

    public function getCategories()
    {
        $lib = new ContentCategory();

        $lib->getCategories();
        $lib->getContents();


        $array = [];

        foreach ($lib->categories as $cat) {

            $array[$cat['label']] = [];

            foreach ($lib->contents[$cat['code']] as $cont) {

                $array[$cat['label']][$cont['label']] = $cont['code'];

            }

        }

        return $array;
    }

}