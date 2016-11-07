<?php

namespace OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Transaction
 *
 * @ORM\Table(name="transaction")
 * @ORM\Entity(repositoryClass="OrderBundle\Repository\TransactionRepository")
 */
class Transaction
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
     * @var \DateTime
     *
     * @ORM\Column(name="datePayIn", type="datetime", nullable=true)
     */
    private $datePayIn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datePayOut", type="datetime", nullable=true)
     */
    private $datePayOut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateTransaction", type="datetime")
     */
    private $dateTransaction;

    /**
     * @var bool
     *
     * @ORM\Column(name="emc", type="boolean")
     */
    private $emc;

    /**
     * @ORM\ManyToOne(targetEntity="OrderBundle\Entity\Order")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(name="totalPrice", type="decimal", precision=10, scale=2)
     */
    private $totalPrice;

    /**
     * @var String
     *
     * @ORM\Column(name="deliveryPrice", type="decimal", precision=10, scale=2)
     */
    private $deliveryPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="totalProductPrice", type="decimal", precision=10, scale=2)
     */
    private $totalProductPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="productPrice", type="decimal", precision=10, scale=2)
     */
    private $productPrice;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="buyer_id", referencedColumnName="id")
     */
    private $buyer;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="seller_id", referencedColumnName="id")
     */
    private $seller;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string")
     */
    private $type = 'payin';



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
     * Set datePayIn
     *
     * @param \DateTime $datePayIn
     *
     * @return Transaction
     */
    public function setDatePayIn($datePayIn)
    {
        $this->datePayIn = $datePayIn;

        return $this;
    }

    /**
     * Get datePayIn
     *
     * @return \DateTime
     */
    public function getDatePayIn()
    {
        return $this->datePayIn;
    }

    /**
     * Set datePayOut
     *
     * @param \DateTime $datePayOut
     *
     * @return Transaction
     */
    public function setDatePayOut($datePayOut)
    {
        $this->datePayOut = $datePayOut;

        return $this;
    }

    /**
     * Get datePayOut
     *
     * @return \DateTime
     */
    public function getDatePayOut()
    {
        return $this->datePayOut;
    }

    /**
     * Set dateTransaction
     *
     * @param \DateTime $dateTransaction
     *
     * @return Transaction
     */
    public function setDateTransaction($dateTransaction)
    {
        $this->dateTransaction = $dateTransaction;

        return $this;
    }

    /**
     * Get dateTransaction
     *
     * @return \DateTime
     */
    public function getDateTransaction()
    {
        return $this->dateTransaction;
    }

    /**
     * Set emc
     *
     * @param boolean $emc
     *
     * @return Transaction
     */
    public function setEmc($emc)
    {
        $this->emc = $emc;

        return $this;
    }

    /**
     * Get emc
     *
     * @return bool
     */
    public function getEmc()
    {
        return $this->emc;
    }

    /**
     * Set totalPrice
     *
     * @param string $totalPrice
     *
     * @return Transaction
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * Get totalPrice
     *
     * @return string
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * Set deliveryPrice
     *
     * @param \DateTime $deliveryPrice
     *
     * @return Transaction
     */
    public function setDeliveryPrice($deliveryPrice)
    {
        $this->deliveryPrice = $deliveryPrice;

        return $this;
    }

    /**
     * Get deliveryPrice
     *
     * @return \DateTime
     */
    public function getDeliveryPrice()
    {
        return $this->deliveryPrice;
    }

    /**
     * Set totalProductPrice
     *
     * @param string $totalProductPrice
     *
     * @return Transaction
     */
    public function setTotalProductPrice($totalProductPrice)
    {
        $this->totalProductPrice = $totalProductPrice;

        return $this;
    }

    /**
     * Get totalProductPrice
     *
     * @return string
     */
    public function getTotalProductPrice()
    {
        return $this->totalProductPrice;
    }

    /**
     * Set productPrice
     *
     * @param string $productPrice
     *
     * @return Transaction
     */
    public function setProductPrice($productPrice)
    {
        $this->productPrice = $productPrice;

        return $this;
    }

    /**
     * Get productPrice
     *
     * @return string
     */
    public function getProductPrice()
    {
        return $this->productPrice;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return mixed
     */
    public function getBuyer()
    {
        return $this->buyer;
    }

    /**
     * @param mixed $buyer
     */
    public function setBuyer($buyer)
    {
        $this->buyer = $buyer;
    }

    /**
     * @return mixed
     */
    public function getSeller()
    {
        return $this->seller;
    }

    /**
     * @param mixed $seller
     */
    public function setSeller($seller)
    {
        $this->seller = $seller;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

}

