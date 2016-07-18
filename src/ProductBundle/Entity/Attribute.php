<?php

namespace ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Attribute
 *
 * @ORM\Table(name="attribute")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\AttributeRepository")
 */
class Attribute
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
     * @ORM\ManyToOne(targetEntity="AttributeType")
     * @ORM\JoinColumn(name="attribute_type_id", referencedColumnName="id")
     */
    private $attributeType;

    /**
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="attributes")
     * @ORM\JoinTable(name="categories_attributes")
     */
    private $categories;

    /**
     * @ORM\ManyToOne(targetEntity="Referential")
     * @ORM\JoinColumn(name="referential_id", referencedColumnName="id")
     */
    private $referential;

    /**
     * @ORM\ManyToOne(targetEntity="AttributeSearchTemplate")
     * @ORM\JoinColumn(name="attribute_search_template_id", referencedColumnName="id")
     */
    private $attributeSearchTemplate;

    public function __construct() {
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * @return Attribute
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
     * @return Attribute
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

    public function __toString() {
        return $this->getCode();
    }


    /**
     * Set attributeType
     *
     * @param \ProductBundle\Entity\AttributeType $attributeType
     *
     * @return Attribute
     */
    public function setAttributeType(\ProductBundle\Entity\AttributeType $attributeType = null)
    {
        $this->attributeType = $attributeType;

        return $this;
    }

    /**
     * Get attributeType
     *
     * @return \ProductBundle\Entity\AttributeType
     */
    public function getAttributeType()
    {
        return $this->attributeType;
    }

    /**
     * Set category
     *
     * @param \ProductBundle\Entity\Category $category
     *
     * @return Attribute
     */
    public function setCategory(\ProductBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \ProductBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add category
     *
     * @param \ProductBundle\Entity\Category $category
     *
     * @return Attribute
     */
    public function addCategory(\ProductBundle\Entity\Category $category)
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \ProductBundle\Entity\Category $category
     */
    public function removeCategory(\ProductBundle\Entity\Category $category)
    {
        $this->categories->removeElement($category);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set referential
     *
     * @param \ProductBundle\Entity\Referential $referential
     *
     * @return Attribute
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

    /**
     * Set attributeSearchTemplate
     *
     * @param \ProductBundle\Entity\AttributeSearchTemplate $attributeSearchTemplate
     *
     * @return Attribute
     */
    public function setAttributeSearchTemplate(\ProductBundle\Entity\AttributeSearchTemplate $attributeSearchTemplate = null)
    {
        $this->attributeSearchTemplate = $attributeSearchTemplate;

        return $this;
    }

    /**
     * Get attributeSearchTemplate
     *
     * @return \ProductBundle\Entity\AttributeSearchTemplate
     */
    public function getAttributeSearchTemplate()
    {
        return $this->attributeSearchTemplate;
    }
}
