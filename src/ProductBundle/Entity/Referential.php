<?php

namespace ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Referential
 *
 * @ORM\Table(name="referential")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\ReferentialRepository")
 */
class Referential
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
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="ReferentialValue", inversedBy="referentials")
     * @ORM\JoinTable(name="referentials_referential_values")
     */
    private $referentialValues;


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
     * Set code
     *
     * @param string $code
     *
     * @return Referential
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
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
     * Set name
     *
     * @param string $name
     *
     * @return Referential
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->referentialValues = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add referentialValue
     *
     * @param \ProductBundle\Entity\ReferentialValue $referentialValue
     *
     * @return Referential
     */
    public function addReferentialValue(\ProductBundle\Entity\ReferentialValue $referentialValue)
    {
        $this->referentialValues[] = $referentialValue;

        return $this;
    }

    /**
     * Remove referentialValue
     *
     * @param \ProductBundle\Entity\ReferentialValue $referentialValue
     */
    public function removeReferentialValue(\ProductBundle\Entity\ReferentialValue $referentialValue)
    {
        $this->referentialValues->removeElement($referentialValue);
    }

    /**
     * Get referentialValues
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferentialValues()
    {
        return $this->referentialValues;
    }

    public function __toString() {
        return $this->getName().' ('.$this->getCode().')';
    }
}
