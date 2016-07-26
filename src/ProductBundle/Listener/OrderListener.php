<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 25/07/16
 * Time: 16:50
 */

namespace ProductBundle\Listener;


use OrderBundle\Entity\Order;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\DateTime;

class OrderListener
{

    private $mangopayService;

    private $container;

    public function __construct(Container $container)
    {
        $this->mangopayService = $container->get('mangopay_service');
        $this->container       = $container;
    }

    public function listen(Request $request, Order $order)
    {
        if ($request->get('action')) {

            $methodName = $request->get('action');

            if(method_exists($this, $methodName)) {
                $this->$methodName($request, $order);
            }

        }

    }

    public function getConnectedUser()
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        return $user;
    }

    public function validateOrder(Request $request, Order $order)
    {

        if ($order->getProduct()->getUser() != $this->getConnectedUser() || $order->getStatus()->getName() != 'pending') {
            throw new NotFoundHttpException();
        }

        $totalAmount = $this->container
            ->get('doctrine')
            ->getRepository('OrderBundle:Order')
            ->getTotalAmount($order->getMangopayPreauthorizationId());

        $payInId = $this->mangopayService->createPayIn($order->getUser(), $order, $totalAmount);

        if ($payInId !== false) {
            $order->setMangopayPayinId($payInId);
            $order->setMangopayPayinDate(new \DateTime());

            /**
             * DUPLICATE PRODUCT
             */
            if ($request->get('keep_published')) {
                $duplicateProduct = clone $order->getProduct();
                $this->container->get('doctrine')->getManager()->persist($duplicateProduct);
            }

            $statusAccepted = $this->container->get('doctrine')->getRepository('OrderBundle:Status')->findOneByName('accepted');

            $order->setStatus($statusAccepted);

            $this->container->get('doctrine')->getManager()->persist($order);
            $this->container->get('doctrine')->getManager()->flush();
        }
    }

    public function canceledOrder(Request $request, Order $order)
    {

        $updateStatus = false;

        if ($order->getProduct()->getUser() != $this->getConnectedUser() || $order->getStatus()->getName() != 'pending') {
            throw new NotFoundHttpException();
        }

        $preAuth = $this->mangopayService->checkStatusPreAuth($order->getMangopayPreauthorizationId());

        if ($preAuth->PayInId == null) {

            $updateStatus = true;

        } else {

            /**
             * Refund
             */
            $refund = $this->mangopayService->refundOrder($order->getUser()->getMangopayUserId(), $preAuth->PayInId, $order->getAmount());
            if ($refund) {

                $updateStatus =  true;

            }


        }

        if ($updateStatus) {


            $statusCanceled = $this->container->get('doctrine')->getRepository('OrderBundle:Status')->findOneByName('canceled');

            $order->setStatus($statusCanceled);


            $this->container->get('doctrine')->getManager()->persist($order);
            $this->container->get('doctrine')->getManager()->flush();
        }


    }



}