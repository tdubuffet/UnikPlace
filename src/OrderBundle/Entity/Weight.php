<?php

namespace OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Weight
 *
 * @ORM\Table(name="weight")
 * @ORM\Entity(repositoryClass="OrderBundle\Repository\WeightRepository")
 */
class Weight
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
     * @var int
     *
     * @ORM\Column(name="minimum", type="integer")
     */
    private $minimum;

    /**
     * @var int
     *
     * @ORM\Column(name="maximum", type="integer")
     */
    private $maximum;

    /**
     * @ORM\ManyToOne(targetEntity="OrderBundle\Entity\Delivery")
     * @ORM\JoinColumn(name="delivery_id", referencedColumnName="id")
     */
    private $delivery;

    /**
     * @ORM\ManyToOne(targetEntity="ProductBundle\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;


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
     * Set minimum
     *
     * @param integer $minimum
     *
     * @return Weight
     */
    public function setMinimum($minimum)
    {
        $this->minimum = $minimum;

        return $this;
    }

    /**
     * Get minimum
     *
     * @return int
     */
    public function getMinimum()
    {
        return $this->minimum;
    }

    /**
     * Set maximum
     *
     * @param integer $maximum
     *
     * @return Weight
     */
    public function setMaximum($maximum)
    {
        $this->maximum = $maximum;

        return $this;
    }

    /**
     * Get maximum
     *
     * @return int
     */
    public function getMaximum()
    {
        return $this->maximum;
    }



    /**
     * Set delivery
     *
     * @param \OrderBundle\Entity\Delivery $delivery
     *
     * @return Weight
     */
    public function setDelivery(\OrderBundle\Entity\Delivery $delivery = null)
    {
        $this->delivery = $delivery;

        return $this;
    }

    /**
     * Get delivery
     *
     * @return \OrderBundle\Entity\Delivery
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * Set product
     *
     * @param \ProductBundle\Entity\Product $product
     *
     * @return Weight
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
}
