<?php

namespace FOS2CommentBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FOS2CommentBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSCommentBundle';
    }
}
