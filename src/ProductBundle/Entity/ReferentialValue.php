<?php

namespace ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReferentialValue
 *
 * @ORM\Table(name="referential_value")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\ReferentialValueRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class ReferentialValue
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
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;

    /**
     * @ORM\ManyToMany(targetEntity="Referential", mappedBy="referentialValues")
     */
    private $referentials;

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
     * Set value
     *
     * @param string $value
     *
     * @return ReferentialValue
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    public function __toString() {
        return $this->getValue();
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->referentials = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add referential
     *
     * @param \ProductBundle\Entity\Referential $referential
     *
     * @return ReferentialValue
     */
    public function addReferential(\ProductBundle\Entity\Referential $referential)
    {
        $referential->addReferentialValue($this);
        $this->referentials[] = $referential;

        return $this;
    }

    /**
     * Remove referential
     *
     * @param \ProductBundle\Entity\Referential $referential
     */
    public function removeReferential(\ProductBundle\Entity\Referential $referential)
    {
        $this->referentials->removeElement($referential);
        $referential->removeReferentialValue($this);
    }

    /**
     * Get referentials
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferentials()
    {
        return $this->referentials;
    }
}
