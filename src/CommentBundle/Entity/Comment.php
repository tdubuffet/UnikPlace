<?php

namespace CommentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use UserBundle\Entity\User;

/**
 * Comment
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="CommentBundle\Repository\CommentRepository")
 */
class Comment
{
    use ORMBehaviors\Timestampable\Timestampable;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     */
    private $message;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var ThreadComment
     * @ORM\ManyToOne(targetEntity="CommentBundle\Entity\ThreadComment")
     * @ORM\JoinColumn(name="thread_comment_id", referencedColumnName="id")
     */
    private $thread;

    /**
     * @ORM\ManyToOne(targetEntity="CommentBundle\Entity\Comment", inversedBy="children")
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id", nullable=true)
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="CommentBundle\Entity\Comment", mappedBy="parent")
     */
    private $children;

    /**
     * @var boolean
     * @ORM\Column(name="is_validated", type="boolean")
     */
    private $isValidated;

    /**
     * @var boolean
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    private $isDeleted;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return ThreadComment
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @param ThreadComment $thread
     */
    public function setThread($thread)
    {
        $this->thread = $thread;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildrenValidated()
    {
        $childs = new ArrayCollection();
        /** @var Comment $child */
        foreach($this->children as $child) {
            if ($child->getIsValidated() == true && $child->getIsDeleted() == false) {
                $childs->add($child);
            }
        }

        return $childs;
    }

    /**
     * @param mixed $children
     * @return Comment
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsValidated()
    {
        return $this->isValidated;
    }

    /**
     * @param boolean $isValidated
     * @return Comment
     */
    public function setIsValidated($isValidated)
    {
        $this->isValidated = $isValidated;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * @param boolean $isDeleted
     * @return Comment
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }
}

