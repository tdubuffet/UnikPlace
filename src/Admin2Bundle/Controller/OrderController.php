<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 29/08/16
 * Time: 11:20
 */

namespace Admin2Bundle\Controller;

use OrderBundle\Entity\Order;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OrderController
 * @package Admin2Bundle\Controller
 * @Route("/orders")
 */
class OrderController extends Controller
{
    /**
     * @Route("/", name="ad2_orders_list")
     * @return Response
     */
    public function orderListAction()
    {
        $orders = $this->getDoctrine()->getRepository("OrderBundle:Order")->findAll();

        return $this->render("Admin2Bundle:Orders:list.html.twig", ['orders' => $orders]);
    }

    /**
     * @Route("/{id}", name="ad2_orders_view")
     * @param Order $order
     * @return Response
     */
    public function detailOrderAction(Order $order)
    {
        return $this->render("Admin2Bundle:Orders:view.html.twig", ['order' => $order]);
    }

}