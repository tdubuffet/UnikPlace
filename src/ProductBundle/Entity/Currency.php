<?php

namespace ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Currency
 *
 * @ORM\Table(name="currency")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\CurrencyRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Currency
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Assert\Length(
     *      min = 3,
     *      max = 3,
     * )
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     *
     * @ORM\Column(name="code", type="string", length=3, unique=true)
     * @var string
     */
    protected $code;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     *
     * @ORM\Column(name="rate", type="decimal", precision=10, scale=4)
     * @var string
     */
    protected $rate;

    /**
     * Get ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
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
     * Set rate
     *
     * @param string $rate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
    }

    /**
     * Convert currency rate
     *
     * @param float $rate
     */
    public function convert($rate)
    {
        $this->rate /= $rate;
    }

    public function __toString() {
        return $this->code;
    }
}
