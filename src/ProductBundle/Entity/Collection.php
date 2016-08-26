<?php

namespace ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * Collection
 *
 * @ORM\Table(name="collection")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\CollectionRepository")
 */
class Collection
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
     * @var ArrayCollection $categories
     * @ORM\ManyToMany(targetEntity="ProductBundle\Entity\Category", inversedBy="collections")
     * @ORM\JoinTable(name="category_collection")
     */
    private $categories;

    /**
     * @ORM\OneToOne(targetEntity="CollectionImage", mappedBy="collection", cascade={"persist"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private $image;

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
     * @var ArrayCollection $products
     * @ORM\ManyToMany(targetEntity="ProductBundle\Entity\Product", inversedBy="collections")
     * @ORM\JoinTable(name="product_collection")
     */
    private $products;

    /**
     * Collection constructor.
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Collection
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Collection
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param Category $category
     * @return Collection
     */
    public function addCategory(Category $category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param Category $category
     *
     * @return Collection
     */
    public function removeCategories(Category $category)
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * @param Product $product
     * @return Collection
     */
    public function addProduct(Product $product)
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param Product $product
     *
     * @return Collection
     */
    public function removeProduct(Product $product)
    {
        $this->products->removeElement($product);

        return $this;
    }

    /**
     * @return CollectionImage
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param CollectionImage $image
     * @return Collection
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return array
     */
    public function getSluggableFields()
    {
        return [ 'name' ];
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
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name;
    }
}
