<?php

namespace OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransactionPayIn
 *
 * @ORM\Table(name="transaction_pay_transfert")
 * @ORM\Entity(repositoryClass="OrderBundle\Repository\TransactionPayTransfertRepository")
 */
class TransactionPayTransfert
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
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="amount_without_fees", type="decimal", precision=10, scale=2)
     */
    private $amountWithoutFees;


    /**
     * @var string
     *
     * @ORM\Column(name="fees", type="decimal", precision=10, scale=2)
     */
    private $fees;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;
    
    /**
     * @ORM\ManyToOne(targetEntity="OrderBundle\Entity\Order")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;


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
     * @return TransactionPayIn
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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return TransactionPayIn
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
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
     * @return string
     */
    public function getAmountWithoutFees()
    {
        return $this->amountWithoutFees;
    }

    /**
     * @param string $amountWithoutFees
     */
    public function setAmountWithoutFees($amountWithoutFees)
    {
        $this->amountWithoutFees = $amountWithoutFees;
    }

    /**
     * @return string
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * @param string $fees
     */
    public function setFees($fees)
    {
        $this->fees = $fees;
    }

    public function getMessage()
    {
        return '';
    }
}

