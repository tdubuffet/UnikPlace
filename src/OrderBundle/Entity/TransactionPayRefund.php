<?php

namespace OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransactionPayIn
 *
 * @ORM\Table(name="transaction_pay_refund")
 * @ORM\Entity(repositoryClass="OrderBundle\Repository\TransactionPayRefundRepository")
 */
class TransactionPayRefund
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
     * delivery, product, all
     * @ORM\Column(name="type", type="string")
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     */
    private $amount;

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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
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

    public function getMessage()
    {
        if ($this->getType() == 'all' ) {
            return 'Remboursement total de la commande. Versement de ' . number_format($this->getAmount(), 2, ',', ' ') . '€ le ' . $this->getDate()->format('d/m/Y H:i');
        }

        if ($this->getType() == 'delivery' ) {
            return 'Remboursement de la livraison. Versement de ' . number_format($this->getAmount(), 2, ',', ' ') . '€ le ' . $this->getDate()->format('d/m/Y H:i');
        }

        if ($this->getType() == 'product' ) {
            return 'Remboursement d\'un produit de la commande. Versement de ' . number_format($this->getAmount(), 2, ',', ' ') . '€ le ' . $this->getDate()->format('d/m/Y H:i');
        }
    }
}

