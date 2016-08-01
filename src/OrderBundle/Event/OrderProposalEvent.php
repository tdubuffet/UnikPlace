<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 01/08/16
 * Time: 11:25
 */

namespace OrderBundle\Event;

use OrderBundle\Entity\OrderProposal;
use Symfony\Component\EventDispatcher\Event;

class OrderProposalEvent extends Event
{
    private $proposal;

    public function __construct(OrderProposal $proposal)
    {
        $this->proposal = $proposal;
    }

    /**
     * @return OrderProposal
     */
    public function getProposal()
    {
        return $this->proposal;
    }

}