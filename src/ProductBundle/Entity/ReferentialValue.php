<?php

namespace ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReferentialValue
 *
 * @ORM\Table(name="referential_value")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\ReferentialValueRepository")
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
     * @ORM\ManyToOne(targetEntity="Referential", inversedBy="referential_values")
     * @ORM\JoinColumn(name="referential_id", referencedColumnName="id")
     */
    private $referential;

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

    /**
     * Set referential
     *
     * @param \ProductBundle\Entity\Referential $referential
     *
     * @return ReferentialValue
     */
    public function setReferential(\ProductBundle\Entity\Referential $referential = null)
    {
        $this->referential = $referential;

        return $this;
    }

    /**
     * Get referential
     *
     * @return \ProductBundle\Entity\Referential
     */
    public function getReferential()
    {
        return $this->referential;
    }

    public function __toString() {
        return $this->getValue();
    }
}
