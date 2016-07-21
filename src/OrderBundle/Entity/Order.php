<?php

namespace OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * Order
 *
 * @ORM\Table(name="product_order")
 * @ORM\Entity(repositoryClass="OrderBundle\Repository\OrderRepository")
 */
class Order
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
     * @ORM\Column(name="amount", type="decimal", precision=8, scale=2)
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity="ProductBundle\Entity\Currency")
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="id")
     */
    private $currency;

    /**
     * @ORM\ManyToMany(targetEntity="ProductBundle\Entity\Product")
     * @ORM\JoinTable(name="orders_products",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="order_id", referencedColumnName="id")}
     *      )
     */
    private $products;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="orders")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="LocationBundle\Entity\Address")
     * @ORM\JoinColumn(name="delivery_address_id", referencedColumnName="id")
     */
    private $delivery_address;

    /**
     * @ORM\ManyToOne(targetEntity="LocationBundle\Entity\Address")
     * @ORM\JoinColumn(name="billing_address_id", referencedColumnName="id")
     */
    private $billing_address;

    /**
     * @ORM\ManyToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="mangopay_preauthorization_id", type="string", length=255)
     */
    private $mangopay_preauthorization_id;


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
     * Set amount
     *
     * @param string $amount
     *
     * @return Order
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set mangopayPreauthorizationId
     *
     * @param string $mangopayPreauthorizationId
     *
     * @return Order
     */
    public function setMangopayPreauthorizationId($mangopayPreauthorizationId)
    {
        $this->mangopay_preauthorization_id = $mangopayPreauthorizationId;

        return $this;
    }

    /**
     * Get mangopayPreauthorizationId
     *
     * @return string
     */
    public function getMangopayPreauthorizationId()
    {
        return $this->mangopay_preauthorization_id;
    }

    /**
     * Set currency
     *
     * @param \ProductBundle\Entity\Currency $currency
     *
     * @return Order
     */
    public function setCurrency(\ProductBundle\Entity\Currency $currency = null)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return \ProductBundle\Entity\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Add product
     *
     * @param \ProductBundle\Entity\Product $product
     *
     * @return Order
     */
    public function addProduct(\ProductBundle\Entity\Product $product)
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * Remove product
     *
     * @param \ProductBundle\Entity\Product $product
     */
    public function removeProduct(\ProductBundle\Entity\Product $product)
    {
        $this->products->removeElement($product);
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
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Order
     */
    public function setUser(\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set deliveryAddress
     *
     * @param \LocationBundle\Entity\Address $deliveryAddress
     *
     * @return Order
     */
    public function setDeliveryAddress(\LocationBundle\Entity\Address $deliveryAddress = null)
    {
        $this->delivery_address = $deliveryAddress;

        return $this;
    }

    /**
     * Get deliveryAddress
     *
     * @return \LocationBundle\Entity\Address
     */
    public function getDeliveryAddress()
    {
        return $this->delivery_address;
    }

    /**
     * Set billingAddress
     *
     * @param \LocationBundle\Entity\Address $billingAddress
     *
     * @return Order
     */
    public function setBillingAddress(\LocationBundle\Entity\Address $billingAddress = null)
    {
        $this->billing_address = $billingAddress;

        return $this;
    }

    /**
     * Get billingAddress
     *
     * @return \LocationBundle\Entity\Address
     */
    public function getBillingAddress()
    {
        return $this->billing_address;
    }

    /**
     * Set status
     *
     * @param \OrderBundle\Entity\Status $status
     *
     * @return Order
     */
    public function setStatus(\OrderBundle\Entity\Status $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \OrderBundle\Entity\Status
     */
    public function getStatus()
    {
        return $this->status;
    }
}
