<?php

namespace MessageBundle\MessageBuilder;

use Doctrine\Common\Collections\Collection;
use FOS\MessageBundle\Model\MessageInterface;
use FOS\MessageBundle\Model\ParticipantInterface;
use FOS\MessageBundle\Sender\SenderInterface;

/**
 * Fluent interface message builder for new thread messages
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class NewThreadMessageBuilder extends \FOS\MessageBundle\MessageBuilder\NewThreadMessageBuilder
{
    /**
     * The thread product
     *
     * @param  string
     * @return NewThreadMessageBuilder (fluent interface)
     */
    public function setProduct($product)
    {
        $this->thread->setProduct($product);

        return $this;
    }

}
