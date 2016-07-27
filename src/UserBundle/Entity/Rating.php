<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_rating")
 */
class Rating
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="rated_user_id", referencedColumnName="id")
     */
    private $ratedUser;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="author_user_id", referencedColumnName="id")
     */
    private $authorUser;

    /**
     * @ORM\ManyToOne(targetEntity="OrderBundle\Entity\Order")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;


    /**
     * @ORM\Column(name="rate", type="integer", nullable=false)
     * @Assert\NotBlank(message="Merci de laisser une note")
     */
    private $rate;

    /**
     * @ORM\Column(name="message", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="Merci de laisser un commentaire")
     * @Assert\Length(min=3, max=255)
     */
    private $message;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set rate
     *
     * @param integer $rate
     *
     * @return Rating
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return integer
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Rating
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set ratedUser
     *
     * @param \UserBundle\Entity\User $ratedUser
     *
     * @return Rating
     */
    public function setRatedUser(\UserBundle\Entity\User $ratedUser = null)
    {
        $this->ratedUser = $ratedUser;

        return $this;
    }

    /**
     * Get ratedUser
     *
     * @return \UserBundle\Entity\User
     */
    public function getRatedUser()
    {
        return $this->ratedUser;
    }

    /**
     * Set authorUser
     *
     * @param \UserBundle\Entity\User $authorUser
     *
     * @return Rating
     */
    public function setAuthorUser(\UserBundle\Entity\User $authorUser = null)
    {
        $this->authorUser = $authorUser;

        return $this;
    }

    /**
     * Get authorUser
     *
     * @return \UserBundle\Entity\User
     */
    public function getAuthorUser()
    {
        return $this->authorUser;
    }

    /**
     * Set order
     *
     * @param \OrderBundle\Entity\Order $order
     *
     * @return Rating
     */
    public function setOrder(\OrderBundle\Entity\Order $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return \OrderBundle\Entity\Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}
