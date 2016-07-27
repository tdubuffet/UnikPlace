<?php

namespace OrderBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use OrderBundle\Entity\Order;

class OrderEvent extends Event
{
    const NAME = 'order.event';

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }
}