<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 28/07/16
 * Time: 10:48
 */

namespace DeliveryBundle\Controller;


use AppBundle\Form\ContactForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class WebServiceController extends Controller
{
    /**
     * @Route("/emc/webservice", name="emc_tracking")
     * @param Request $request
     */
    public function emcPushUrlAction(Request $request)
    {

        $orderNumber = $request->get('order');
        $keyNumber   = $request->get('key');

        if(!$orderNumber || !$keyNumber) {
            throw new \Exception('Not valid push url');
        }

        if ($keyNumber != md5('emc_delivery')) {
            throw new \Exception('Key is not valid');
        }

        $order = $this->getDoctrine()->getRepository('OrderBundle:Order')->findOneById($orderNumber);

        if (!$order){
            throw new \Exception('Not valid order url');
        }

        if ($request->get('type') == 'tracking') {

            $dump = [
                'text'          => $request->get('text'),
                'localisation'  => $request->get('localisation'),
                'etat'          => $request->get('etat'),
                'date'          => $request->get('date')
            ];

            $order->setEmcTracking($dump);

        } else {


            $dump = [
                'emc_reference'         => $request->get('emc_reference'),
                'carrier_reference'     => $request->get('carrier_reference'),
                'label_url'             => $request->get('label_url'),
                'proforma'              => $request->get('proforma'),
                'remise'                => $request->get('remise'),
                'manifest'              => $request->get('manifest'),
                'connote'               => $request->get('connote'),
                'b13a'                  => $request->get('b13a'),
            ];

            $order->setEmcStatus($dump);

        }

        $this->getDoctrine()->getManager()->persist($order);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(['status' => 'OK']);
    }

}