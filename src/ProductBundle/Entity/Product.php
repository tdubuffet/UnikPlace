<?php

namespace ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\ProductRepository")
 */
class Product
{
    use ORMBehaviors\Sluggable\Sluggable,
        ORMBehaviors\Timestampable\Timestampable;

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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=8, scale=2)
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Currency")
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="id")
     */
    private $currency;

    /**
     * @ORM\ManyToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="AttributeValue", mappedBy="product")
     */
    private $attributeValues;

    /**
     * @ORM\OneToMany(targetEntity="Image", mappedBy="product")
     */
    private $images;

    public function __construct() {
        $this->images = new ArrayCollection();
        $this->attributesValues = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Product
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
     * Set description
     *
     * @param string $description
     *
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set currency
     *
     * @param \ProductBundle\Entity\Currency $currency
     *
     * @return Product
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
     * Set status
     *
     * @param \ProductBundle\Entity\Status $status
     *
     * @return Product
     */
    public function setStatus(\ProductBundle\Entity\Status $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \ProductBundle\Entity\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getSluggableFields()
    {
        return [ 'name' ];
    }


    /**
     * Add image
     *
     * @param \ProductBundle\Entity\Image $image
     *
     * @return Product
     */
    public function addImage(\ProductBundle\Entity\Image $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param \ProductBundle\Entity\Image $image
     */
    public function removeImage(\ProductBundle\Entity\Image $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    public function __toString() {
        return $this->name;
    }


    /**
     * Set category
     *
     * @param \ProductBundle\Entity\Category $category
     *
     * @return Product
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
     * Get breadcrumb of the product
     *
     * @return array
     */
    public function getBreadcrumb()
    {
        $breadcrumb = array();
        $category = $this->getCategory();
        while (isset($category)) {
            $breadcrumb[] = $category;
            $category = $category->getParent();
        }
        return array_reverse($breadcrumb);
    }

    /**
     * Add attributeValue
     *
     * @param \ProductBundle\Entity\AttributeValue $attributeValue
     *
     * @return Product
     */
    public function addAttributeValue(\ProductBundle\Entity\AttributeValue $attributeValue)
    {
        $this->attributeValues[] = $attributeValue;

        return $this;
    }

    /**
     * Remove attributeValue
     *
     * @param \ProductBundle\Entity\AttributeValue $attributeValue
     */
    public function removeAttributeValue(\ProductBundle\Entity\AttributeValue $attributeValue)
    {
        $this->attributeValues->removeElement($attributeValue);
    }

    /**
     * Get attributeValues
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttributeValues()
    {
        return $this->attributeValues;
    }

    /*
     * Get product attributes directly
     *
     * @return array
     */
    public function getAttributes($em)
    {
        $attributes = array();
        foreach ($this->attributeValues as $value) {
            $attribute = $value->getAttribute();
            $attributes[$attribute->getCode()] = ['name'  => $attribute->getName(),
                                                  'value' => $this->getAttributebyCode($attribute->getCode(), $em)];
        }
        return $attributes;
    }

    /*
     * Get product attribute by code
     *
     * @return mixed
     */
    public function getAttributebyCode($code, $em)
    {
        // Find attribute types
        $objectTypes = $em->getRepository('ProductBundle:AttributeType')->findAll();
        $types = array();
        foreach ($objectTypes as $type) {
            $types[$type->getId()] = $type->getName();
        }
        $attribute = $em->getRepository('ProductBundle:Attribute')->findOneByCode($code);
        if (!isset($attribute)) {
            return null;
        }
        $attributeValue = $em->getRepository('ProductBundle:AttributeValue')->findOneBy(
            ['attribute' => $attribute, 'product' => $this]);
        if (!isset($attributeValue)){
            return null;
        }
        // Find value
        $valueTypes = ['text', 'boolean', 'integer', 'float', 'datetime', 'date', 'referential'];
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($valueTypes as $valueType) {
            $value = $accessor->getValue($attributeValue, $valueType.'_value');
            if (!is_null($value)) {
                if ($value instanceof ReferentialValue) {
                    $value = $value->getValue();
                }
                // Cast to the right type
                $type = $attribute->getAttributeType()->getName();
                if (isset($type) && in_array($type, ['string', 'boolean', 'integer', 'float'])) {
                    settype($value, $type);
                }
                return $value;
            }
        }
        return null;
    }
}
