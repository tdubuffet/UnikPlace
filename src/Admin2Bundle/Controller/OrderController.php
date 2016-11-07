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
use Symfony\Component\HttpFoundation\Request;
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
    public function detailOrderAction(Request $request, Order $order)
    {

        $refund = $request->get('refund', false);

        if ($refund)  {
            try{
                $this->get('mangopay_service')->refundOrderByType($order, $refund);

                $this->get('session')->getFlashBag()->add('success', 'Remboursement validé.');
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', $e->getMessage());
            }

            return $this->redirectToRoute('ad2_orders_view', ['id' => $order->getId()]);
        }

        $refundOrder = $this->get('doctrine')->getRepository('OrderBundle:TransactionPayRefund')->findOneBy([
            'order' => $order,
            'type' => 'all'
        ]);

        if ($refundOrder) {
            $refundOrder = true;
        }

        $transactions = array_merge(
            $this->get('doctrine')->getRepository('OrderBundle:TransactionPayIn')->findByOrder($order),
            $this->get('doctrine')->getRepository('OrderBundle:TransactionPayRefund')->findByOrder($order)
        );


        return $this->render("Admin2Bundle:Orders:view.html.twig", [
            'order' => $order,
            'refund' => $refundOrder,
            'transactions' => $transactions
        ]);
    }

}