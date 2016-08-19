<?php

namespace CommentBundle\Event;

use CommentBundle\Entity\Comment;
use Symfony\Component\EventDispatcher\Event;

class CommentEvent extends Event
{
    const NAME = 'comment.event';

    protected $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param Comment $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }
}