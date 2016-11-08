<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 28/07/16
 * Time: 10:48
 */

namespace DeliveryBundle\Controller;


use AppBundle\Form\ContactForm;
use OrderBundle\Entity\Order;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class WebServiceController extends Controller
{

    /**
     * @Route("/emc/document/{id}", name="emc_document_download")
     * @param Request $request
     */
    public function emcDocAction(Request $request, Order $order)
    {

        if ($order->getEmc()) {

            if (isset($order->getEmcInfos()['labels']) && isset($order->getEmcInfos()['labels'][0])) {
                $this->get('delivery.emc')->downloadBordereau($order->getEmcInfos()['labels'][0]);
            }

        }

        throw new \Exception('Not found order');

    }

    /**
     * @Route("/emc/webservice", name="emc_tracking")
     * @param Request $request
     */
    public function emcPushUrlAction(Request $request)
    {

        $orderNumber = $request->get('order');
        $keyNumber   = $request->get('key');

        $logger = $this->get('monolog.logger.api');

        if(!$orderNumber || !$keyNumber) {
            $logger->addNotice('Not valid push url: ' . $orderNumber . ' - ' . $keyNumber);
            throw new \Exception('Not valid push url');
        }

        if ($keyNumber != md5('emc_delivery')) {
            $logger->addNotice('Key is not valid: ' . $orderNumber . ' - ' . $keyNumber);
            throw new \Exception('Key is not valid');
        }

        $order = $this->getDoctrine()->getRepository('OrderBundle:Order')->findOneById($orderNumber);

        if (!$order){
            $logger->addNotice('Not valid order url: ' . $orderNumber . ' - ' . $keyNumber);
            throw new \Exception('Not valid order url');
        }

        if ($request->get('type') == 'tracking') {

            $dump = [
                'text'          => $request->get('text'),
                'localisation'  => $request->get('localisation'),
                'etat'          => $request->get('etat'),
                'date'          => $request->get('date')
            ];

            $values = $order->getEmcTracking();

            if (!isset($values['etat']) || (isset($values['etat']) && $values['etat'] != $dump['etat'])) {

                switch ($values['etat']) {

                    case 'CMD':
                        $this->get('mailer_sender')->sendValidatedEmcToSeller($order);
                        break;

                    case 'ENV':
                        $this->get('mailer_sender')->sendTransitEmcToBuyer($order);
                        break;

                    case 'ANL':

                        //@todo commande annulé: Envoyer un mail ?
                        break;

                    case 'LIV':
                        $this->get('mailer_sender')->sendArrivalEmcToBuyer($order);
                        break;

                }

            }

            $order->setEmcTracking($dump);

            $logger->addNotice('Save Emc Tracking: ' . $orderNumber . ' - ' . $keyNumber);

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



            $logger->addNotice('Save Emc Status: ' . $orderNumber . ' - ' . $keyNumber);
        }

        $this->getDoctrine()->getManager()->persist($order);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(['status' => 'OK']);
    }

}