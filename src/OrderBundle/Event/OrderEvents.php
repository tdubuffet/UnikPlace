<?php

namespace OrderBundle\Event;

/**
 * Declares all events thrown in the OrderBundle
 */
final class OrderEvents
{
    /**
     * The ORDER_CREATED event occurs after an order has been created (just after the buyer payment)
     *
     * @var string
     */
    const ORDER_CREATED = 'order.created';

    /**
     * The ORDER_ACCEPTED event occurs after an order has been accepted by the seller
     *
     * @var string
     */
    const ORDER_ACCEPTED = 'order.accepted';

    /**
     * The ORDER_REFUSED event occurs after an has been refused by the seller
     *
     * @var string
     */
    const ORDER_REFUSED = 'order.refused';

    /**
     * The ORDER_DONE event occurs after an order has been confirmed by the buyer
     *
     * @var string
     */
    const ORDER_DONE = 'order.done';

    /**
     * The ORDER_DISPUTE_OPENED event occurs after a dispute has been opened on an order
     *
     * @var string
     */
    const ORDER_DISPUTE_OPENED = 'order.dispute_opened';

    /**
     * The ORDER_DISPUTE_CLOSED event occurs after a disputed has been closed
     *
     * @var string
     */
    const ORDER_DISPUTE_CLOSED = 'order.dispute_closed';

    /**
     * The ORDER_PROPOSAL_NEW event occurs after an order proposal has been submited
     *
     * @var string
     */
    const ORDER_PROPOSAL_NEW = 'order_proposal.new';

    /**
     * The ORDER_PROPOSAL_NEW event occurs after an order proposal has been updated to accepted or canceled
     *
     * @var string
     */
    const ORDER_PROPOSAL_CHANGE = 'order_proposal.change';
}
