<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 25/07/16
 * Time: 16:50
 */

namespace OrderBundle\Listener;

use OrderBundle\Entity\Order;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderListener
{
    private $mangopayService;

    private $container;

    public function __construct(ContainerInterface $container)
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

    /**
     * @param Request $request
     * @param Order $order
     */
    public function validateOrder(Request $request, Order $order)
    {

        if ($order->getProduct()->getUser() != $this->getConnectedUser() || $order->getStatus()->getName() != 'pending') {
            throw new NotFoundHttpException();
        }

        $this->container->get('order_service')->validateOrder($order);
    }

    /**
     * @param Request $request
     * @param Order $order
     */
    public function canceledOrder(Request $request, Order $order)
    {
        if ($order->getProduct()->getUser() != $this->getConnectedUser() || $order->getStatus()->getName() != 'pending') {
            throw new NotFoundHttpException();
        }

        $this->container->get('order_service')->cancelOrder($order);
    }

    /**
     * @param Request $request
     * @param Order $order
     * @return bool
     */
    public function doneOrder(Request $request, Order $order)
    {
        if ($order->getUser() != $this->getConnectedUser() || $order->getStatus()->getName() != 'accepted') {
            throw new NotFoundHttpException();
        }

        return $this->container->get('order_service')->doneOrder($order);
    }

    /**
     * @param Request $request
     * @param Order $order
     * @throws \Exception
     */
    public function disputeOrder(Request $request, Order $order)
    {
        if ($order->getUser() != $this->getConnectedUser() || $order->getStatus()->getName() != 'accepted') {
            throw new NotFoundHttpException();
        }

        $this->container->get('order_service')->disputeOrder($order);
    }

    /**
     * @param Request $request
     * @param Order $order
     * @throws \Exception
     */
    public function closeDisputeOrder(Request $request, Order $order)
    {
        if ($order->getUser() != $this->getConnectedUser() || $order->getStatus()->getName() != 'disputed') {
            throw new NotFoundHttpException();
        }

        $this->container->get('order_service')->closeDisputeOrder($order);
    }



}