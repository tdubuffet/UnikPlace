<?php

namespace MessageBundle\Composer;

use MessageBundle\MessageBuilder\NewThreadMessageBuilder;


/**
 * Factory for message builders
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class Composer extends \FOS\MessageBundle\Composer\Composer
{
    /**
     * Starts composing a message, starting a new thread
     *
     * @return NewThreadMessageBuilder
     */
    public function newThread()
    {
        $thread = $this->threadManager->createThread();
        $message = $this->messageManager->createMessage();

        return new NewThreadMessageBuilder($message, $thread);
    }

}
