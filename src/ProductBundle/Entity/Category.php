<?php

namespace ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\CategoryRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="category_cache")
 */
class Category
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
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="category_cache")
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="category_cache")
     */
    private $parent;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="Attribute", mappedBy="categories")
     */
    private $attributes;

    /**
     * @var ArrayCollection $collections
     * @ORM\ManyToMany(targetEntity="Collection", mappedBy="categories")
     */
    private $collections;


    public function __construct() {
        $this->children = new ArrayCollection();
        $this->attributes = new ArrayCollection();
        $this->collections = new ArrayCollection();
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
     * @return Category
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

    public function getSluggableFields()
    {
        return [ 'name' ];
    }

    /**
     * Add child
     *
     * @param \ProductBundle\Entity\Category $child
     *
     * @return Category
     */
    public function addChild(Category $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \ProductBundle\Entity\Category $child
     */
    public function removeChild(Category $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param Category $parent
     *
     * @return Category
     */
    public function setParent(Category $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \ProductBundle\Entity\Category
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get complete path
     *
     * @return string
     */
    public function getPath()
    {
        $slugs = array();
        $category = $this;
        while (isset($category)) {
            $slugs[] = $category->getSlug();
            $category = $category->getParent();
        }
        return implode('/', array_reverse($slugs));
    }

    /**
     * Get breadcrumb of the category
     *
     * @return array
     */
    public function getBreadcrumb()
    {
        $breadcrumb = array();
        $category = $this->getParent();
        while (isset($category)) {
            $breadcrumb[] = $category;
            $category = $category->getParent();
        }
        return array_reverse($breadcrumb);
    }

    public function __toString() {
        $names = array();
        $breadcrumb = $this->getBreadCrumb();
        foreach ($breadcrumb as $element) {
            $names[] = $element->getName();
        }
        $names[] = $this->getName();
        return implode(' > ', $names);
    }

    /**
     * Add attribute
     *
     * @param Attribute $attribute
     *
     * @return Category
     */
    public function addAttribute(Attribute $attribute)
    {
        $this->attributes[] = $attribute;

        return $this;
    }

    /**
     * Remove attribute
     *
     * @param Attribute $attribute
     */
    public function removeAttribute(Attribute $attribute)
    {
        $this->attributes->removeElement($attribute);
    }

    /**
     * Get attributes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return ArrayCollection
     */
    public function getCollections()
    {
        return $this->collections;
    }

    /**
     * Add collection
     * @param Collection $collection
     * @return Category
     */
    public function addCollection(Collection $collection)
    {
        $this->collections->add($collection);

        return $this;
    }

    /**
     * Remove collection
     * @param Collection $collection
     * @return Category
     */
    public function removeCollection(Collection $collection)
    {
        $this->collections->removeElement($collection);

        return $this;
    }



}
