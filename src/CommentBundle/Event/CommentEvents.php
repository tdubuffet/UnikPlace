<?php

namespace CommentBundle\Event;

/**
 * Declares all events thrown in the OrderBundle
 */
final class CommentEvents
{
    /**
     * New comment on product
     *
     * @var string
     */
    const PRODUCT_COMMENT = 'product.comment';

    /**
     * Reply of comment on product
     *
     * @var string
     */
    const PRODUCT_COMMENT_REPLY = 'product.comment_reply';

}
