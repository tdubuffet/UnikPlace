<?php

namespace OrderBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Delivery
 *
 * @ORM\Table(name="delivery")
 * @ORM\Entity(repositoryClass="OrderBundle\Repository\DeliveryRepository")
 */
class Delivery
{
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
     * @ORM\Column(name="fee", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $fee;

    /**
     * @ORM\ManyToOne(targetEntity="ProductBundle\Entity\Product", inversedBy="deliveries")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     **/
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="DeliveryMode")
     * @ORM\JoinColumn(name="delivery_mode_id", referencedColumnName="id")
     **/
    private $deliveryMode;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Set fee
     *
     * @param string $fee
     *
     * @return Delivery
     */
    public function setFee($fee)
    {
        $this->fee = $fee;

        return $this;
    }

    /**
     * Get fee
     *
     * @return string
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * Set product
     *
     * @param \ProductBundle\Entity\Product $product
     *
     * @return Delivery
     */
    public function setProduct(\ProductBundle\Entity\Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \ProductBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set deliveryMode
     *
     * @param \OrderBundle\Entity\DeliveryMode $deliveryMode
     *
     * @return Delivery
     */
    public function setDeliveryMode(\OrderBundle\Entity\DeliveryMode $deliveryMode = null)
    {
        $this->deliveryMode = $deliveryMode;

        return $this;
    }

    /**
     * Get deliveryMode
     *
     * @return \OrderBundle\Entity\DeliveryMode
     */
    public function getDeliveryMode()
    {
        return $this->deliveryMode;
    }
}
