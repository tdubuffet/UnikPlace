<?php

namespace OrderBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use OrderBundle\Entity\Order;
use UserBundle\Entity\User;

class UserEvent extends Event
{
    const NAME = 'user.event';

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}