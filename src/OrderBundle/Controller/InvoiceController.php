<?php

namespace OrderBundle\Controller;

use OrderBundle\Entity\Order;
use OrderBundle\Entity\OrderProposal;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InvoiceController extends Controller
{
    /**
     * @Route("/facture/{id}", name="invoice")
     * @Template("OrderBundle:Invoice:index.html.twig")
     * @Security("has_role('ROLE_USER') and (order.getProduct().getUser().getId() == user.getId())")
     * @return array
     */
    public function indexAction(Request $request, Order $order)
    {

        if ($order->getStatus()->getName() != 'done') {
            throw new NotFoundHttpException;
        }

        $feesRate = $this->get('mangopay_service')
            ->getFeeRateFromProductAndOrderAmount(
                $order->getProduct(),
                $order->getProductAmount()
            );

        $feesRate = $feesRate/100;

        $fees = $this->getParameter('mangopay.fixed_fee');

        $totalProduct = $order->getProductAmount() - $fees -($order->getProductAmount() * $feesRate);
        $tvaRate = $this->getParameter('tax.rate')/100;

        return [
            'order' => $order,
            'feesRate' => $feesRate,
            'fees' => $fees,
            'totalProduct' => $totalProduct,
            'tvaRate' => $tvaRate
        ];
    }

}
