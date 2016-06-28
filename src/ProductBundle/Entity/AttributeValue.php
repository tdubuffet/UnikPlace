<?php

namespace ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AttributeValue
 *
 * @ORM\Table(name="attribute_value")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\AttributeValueRepository")
 */
class AttributeValue
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
     * @ORM\Column(name="text_value", type="string", length=255, nullable=true)
     */
    private $textValue;

    /**
     * @var bool
     *
     * @ORM\Column(name="boolean_value", type="boolean", nullable=true)
     */
    private $booleanValue;

    /**
     * @var int
     *
     * @ORM\Column(name="integer_value", type="integer", nullable=true)
     */
    private $integerValue;

    /**
     * @var float
     *
     * @ORM\Column(name="float_value", type="float", nullable=true)
     */
    private $floatValue;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datetime_value", type="datetime", nullable=true)
     */
    private $datetimeValue;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_value", type="date", nullable=true)
     */
    private $dateValue;

    /**
     * @ORM\ManyToOne(targetEntity="ReferentialValue")
     * @ORM\JoinColumn(name="referential_value_id", referencedColumnName="id")
     */
    private $referentialValue;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="attributeValues")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="Attribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     */
    private $attribute;


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
     * Set textValue
     *
     * @param string $textValue
     *
     * @return AttributeValue
     */
    public function setTextValue($textValue)
    {
        $this->textValue = $textValue;

        return $this;
    }

    /**
     * Get textValue
     *
     * @return string
     */
    public function getTextValue()
    {
        return $this->textValue;
    }

    /**
     * Set booleanValue
     *
     * @param boolean $booleanValue
     *
     * @return AttributeValue
     */
    public function setBooleanValue($booleanValue)
    {
        $this->booleanValue = $booleanValue;

        return $this;
    }

    /**
     * Get booleanValue
     *
     * @return bool
     */
    public function getBooleanValue()
    {
        return $this->booleanValue;
    }

    /**
     * Set integerValue
     *
     * @param integer $integerValue
     *
     * @return AttributeValue
     */
    public function setIntegerValue($integerValue)
    {
        $this->integerValue = $integerValue;

        return $this;
    }

    /**
     * Get integerValue
     *
     * @return int
     */
    public function getIntegerValue()
    {
        return $this->integerValue;
    }

    /**
     * Set floatValue
     *
     * @param float $floatValue
     *
     * @return AttributeValue
     */
    public function setFloatValue($floatValue)
    {
        $this->floatValue = $floatValue;

        return $this;
    }

    /**
     * Get floatValue
     *
     * @return float
     */
    public function getFloatValue()
    {
        return $this->floatValue;
    }

    /**
     * Set datetimeValue
     *
     * @param \DateTime $datetimeValue
     *
     * @return AttributeValue
     */
    public function setDatetimeValue($datetimeValue)
    {
        $this->datetimeValue = $datetimeValue;

        return $this;
    }

    /**
     * Get datetimeValue
     *
     * @return \DateTime
     */
    public function getDatetimeValue()
    {
        return $this->datetimeValue;
    }

    /**
     * Set dateValue
     *
     * @param \DateTime $dateValue
     *
     * @return AttributeValue
     */
    public function setDateValue($dateValue)
    {
        $this->dateValue = $dateValue;

        return $this;
    }

    /**
     * Get dateValue
     *
     * @return \DateTime
     */
    public function getDateValue()
    {
        return $this->dateValue;
    }

    /**
     * Set product
     *
     * @param \ProductBundle\Entity\Product $product
     *
     * @return AttributeValue
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

    /**
     * Set attribute
     *
     * @param \ProductBundle\Entity\Attribute $attribute
     *
     * @return AttributeValue
     */
    public function setAttribute(\ProductBundle\Entity\Attribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return \ProductBundle\Entity\Attribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set referentialValue
     *
     * @param \ProductBundle\Entity\ReferentialValue $referentialValue
     *
     * @return AttributeValue
     */
    public function setReferentialValue(\ProductBundle\Entity\ReferentialValue $referentialValue = null)
    {
        $this->referentialValue = $referentialValue;

        return $this;
    }

    /**
     * Get referentialValue
     *
     * @return \ProductBundle\Entity\ReferentialValue
     */
    public function getReferentialValue()
    {
        return $this->referentialValue;
    }
}
