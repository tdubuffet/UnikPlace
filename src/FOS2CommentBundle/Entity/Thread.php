<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 30/08/16
 * Time: 15:57
 */

namespace FOS2CommentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Entity\Thread as BaseThread;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_thread")
 */
class Thread extends BaseThread
{
    /**
     * @var string $id
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $id;
}