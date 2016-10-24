<?php

namespace OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use ProductBundle\Entity\Product;

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
     * @var string
     *
     * @ORM\Column(name="product_amount", type="decimal", precision=8, scale=2)
     */
    private $productAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_amount", type="decimal", precision=8, scale=2)
     */
    private $deliveryAmount;

    /**
     * @ORM\ManyToOne(targetEntity="ProductBundle\Entity\Currency")
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="id")
     */
    private $currency;

    /**
     * @var Product $product
     *
     * @ORM\ManyToOne(targetEntity="ProductBundle\Entity\Product", inversedBy="orders")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="orders")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="LocationBundle\Entity\Address")
     * @ORM\JoinColumn(name="delivery_address_id", referencedColumnName="id")
     *
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private $deliveryAddress;

    /**
     * @ORM\ManyToOne(targetEntity="LocationBundle\Entity\Address")
     * @ORM\JoinColumn(name="billing_address_id", referencedColumnName="id")
     *
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private $billingAddress;

    /**
     * @ORM\ManyToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     *
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="mangopay_preauthorization_id", type="string", length=255)
     */
    private $mangopayPreauthorizationId;

    /**
     * @var string
     *
     * @ORM\Column(name="mangopay_payin_id", type="string", length=255, nullable=true)
     */
    private $mangopayPayinId;

    /**
     * @var string
     *
     * @ORM\Column(name="mangopay_payin_date", type="datetime", nullable=true)
     */
    private $mangopayPayinDate;

    /**
     * @var string
     *
     * @ORM\Column(name="mangopay_refund_id", type="string", length=255, nullable=true)
     */
    private $mangopayRefundId;

    /**
     * @var string
     *
     * @ORM\Column(name="mangopay_transfer_id", type="string", length=255, nullable=true)
     */
    private $mangopayTransferId;

    /**
     * @var string
     *
     * @ORM\Column(name="mangopay_refund_date", type="datetime", nullable=true)
     */
    private $mangopayRefundDate;

    /**
     * @ORM\ManyToOne(targetEntity="OrderBundle\Entity\Delivery")
     * @ORM\JoinColumn(name="delivery_id", referencedColumnName="id")
     */
    private $delivery;

    /**
     * @var string
     *
     * @ORM\Column(name="tax", type="float", nullable=false)
     */
    private $tax = 20;

    /**
     * @var string
     *
     * @ORM\Column(name="rate", type="decimal", precision=8, scale=2, nullable=false)
     */
    private $rate = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="emc_enabled", type="boolean")
     */
    private $emc = false;

    /**
     * @var string
     *
     * @ORM\Column(name="emc_ref", type="string", nullable=true)
     */
    private $emcRef;

    /**
     * @var string
     *
     * @ORM\Column(name="emc_date", type="string", nullable=true)
     */
    private $emcDate;

    /**
     * @var string
     *
     * @ORM\Column(name="emc_infos", type="array", nullable=true)
     */
    private $emcInfos;

    /**
     * @var string
     *
     * @ORM\Column(name="emc_tracking", type="array", nullable=true)
     */
    private $emcTracking;

    /**
     * @var string
     *
     * @ORM\Column(name="emc_status", type="array", nullable=true)
     */
    private $emcStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="error_message", type="text")
     */
    private $errorMessage;

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

    /**
     * Set delivery
     *
     * @param \OrderBundle\Entity\Delivery $delivery
     *
     * @return Order
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
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getProductAmount()
    {
        return $this->productAmount;
    }

    /**
     * @param mixed $productAmount
     * @return Order
     */
    public function setProductAmount($productAmount)
    {
        $this->productAmount = $productAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryAmount()
    {
        return $this->deliveryAmount;
    }

    /**
     * @param string $deliveryAmount
     * @return Order
     */
    public function setDeliveryAmount($deliveryAmount)
    {
        $this->deliveryAmount = $deliveryAmount;

        return $this;
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
        $this->mangopayPreauthorizationId = $mangopayPreauthorizationId;

        return $this;
    }

    /**
     * Get mangopayPreauthorizationId
     *
     * @return string
     */
    public function getMangopayPreauthorizationId()
    {
        return $this->mangopayPreauthorizationId;
    }

    /**
     * Set mangopayPayinId
     *
     * @param string $mangopayPayinId
     *
     * @return Order
     */
    public function setMangopayPayinId($mangopayPayinId)
    {
        $this->mangopayPayinId = $mangopayPayinId;

        return $this;
    }

    /**
     * Get mangopayPayinId
     *
     * @return string
     */
    public function getMangopayPayinId()
    {
        return $this->mangopayPayinId;
    }

    /**
     * Set mangopayPayinDate
     *
     * @param \DateTime $mangopayPayinDate
     *
     * @return Order
     */
    public function setMangopayPayinDate($mangopayPayinDate)
    {
        $this->mangopayPayinDate = $mangopayPayinDate;

        return $this;
    }

    /**
     * Get mangopayPayinDate
     *
     * @return \DateTime
     */
    public function getMangopayPayinDate()
    {
        return $this->mangopayPayinDate;
    }

    /**
     * Set mangopayRefundId
     *
     * @param string $mangopayRefundId
     *
     * @return Order
     */
    public function setMangopayRefundId($mangopayRefundId)
    {
        $this->mangopayRefundId = $mangopayRefundId;

        return $this;
    }

    /**
     * Get mangopayRefundId
     *
     * @return string
     */
    public function getMangopayRefundId()
    {
        return $this->mangopayRefundId;
    }

    /**
     * Set mangopayTransferId
     *
     * @param string $mangopayTransferId
     *
     * @return Order
     */
    public function setMangopayTransferId($mangopayTransferId)
    {
        $this->mangopayTransferId = $mangopayTransferId;

        return $this;
    }

    /**
     * Get mangopayTransferId
     *
     * @return string
     */
    public function getMangopayTransferId()
    {
        return $this->mangopayTransferId;
    }

    /**
     * Set mangopayRefundDate
     *
     * @param \DateTime $mangopayRefundDate
     *
     * @return Order
     */
    public function setMangopayRefundDate($mangopayRefundDate)
    {
        $this->mangopayRefundDate = $mangopayRefundDate;

        return $this;
    }

    /**
     * Get mangopayRefundDate
     *
     * @return \DateTime
     */
    public function getMangopayRefundDate()
    {
        return $this->mangopayRefundDate;
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
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    /**
     * Get deliveryAddress
     *
     * @return \LocationBundle\Entity\Address
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
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
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * Get billingAddress
     *
     * @return \LocationBundle\Entity\Address
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @return string
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param string $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * Set rate
     *
     * @param string $rate
     *
     * @return Order
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return string
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @return string
     */
    public function getEmc()
    {
        return $this->emc;
    }

    /**
     * @param string $emc
     */
    public function setEmc($emc)
    {
        $this->emc = $emc;
    }

    /**
     * @return string
     */
    public function getEmcRef()
    {
        return $this->emcRef;
    }

    /**
     * @param string $emcRef
     */
    public function setEmcRef($emcRef)
    {
        $this->emcRef = $emcRef;
    }

    /**
     * @return string
     */
    public function getEmcDate()
    {
        return $this->emcDate;
    }

    /**
     * @param string $emcDate
     */
    public function setEmcDate($emcDate)
    {
        $this->emcDate = $emcDate;
    }

    /**
     * @return string
     */
    public function getEmcInfos()
    {
        return $this->emcInfos;
    }

    /**
     * @param string $emcInfos
     */
    public function setEmcInfos($emcInfos)
    {
        $this->emcInfos = $emcInfos;
    }

    /**
     * @return string
     */
    public function getEmcTracking()
    {
        return $this->emcTracking;
    }

    /**
     * @param string $emcTracking
     */
    public function setEmcTracking($emcTracking)
    {
        $this->emcTracking = $emcTracking;
    }

    /**
     * @return string
     */
    public function getEmcStatus()
    {
        return $this->emcStatus;
    }

    /**
     * @param string $emcStatus
     */
    public function setEmcStatus($emcStatus)
    {
        $this->emcStatus = $emcStatus;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }
    
}