<?php

namespace ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @var string
     *
     * @ORM\Column(name="original_price", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $originalPrice = null;

    /**
     * @var string
     *
     * @ORM\Column(name="allow_offer", type="boolean")
     */
    private $allowOffer;

    /**
     * @var string
     *
     * @ORM\Column(name="weight", type="integer")
     */
    private $weight;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="products")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Currency")
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="id")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private $currency;

    /**
     * @ORM\ManyToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="AttributeValue", mappedBy="product")
     */
    private $attributeValues;

    /**
     * @ORM\OneToMany(targetEntity="Image", mappedBy="product")
     * @ORM\OrderBy({"sort" = "ASC"})
     */
    private $images;

    /**
     * @ORM\OneToMany(targetEntity="Favorite", mappedBy="product")
     */
    private $favorites;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="products")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="MessageBundle\Entity\Thread", mappedBy="product")
     */
    private $threads;

    /**
     * @var ArrayCollection $collections
     * @ORM\ManyToMany(targetEntity="ProductBundle\Entity\Collection", mappedBy="products")
     */
    private $collections;

    /**
     * @ORM\ManyToOne(targetEntity="LocationBundle\Entity\Address")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private $address;


    /**
     * @ORM\OneToMany(targetEntity="OrderBundle\Entity\Delivery", mappedBy="product")
     **/
    private $deliveries;

    /**
     * @ORM\OneToMany(targetEntity="OrderBundle\Entity\Order", mappedBy="product")
     */
    private $orders;

    /**
     * @var ArrayCollection $proposals
     * @ORM\OneToMany(targetEntity="OrderBundle\Entity\OrderProposal", mappedBy="product")
     */
    private $proposals;

    /**
     * @ORM\OneToOne(targetEntity="OrderBundle\Entity\OrderProposal")
     * @ORM\JoinColumn(name="proposal_accepted_id", referencedColumnName="id", nullable=true)
     */
    private $proposalAccepted;

    /**
     * @var string
     *
     * @ORM\Column(name="width", type="decimal", precision=5, scale=2)
     */
    private $width;

    /**
     * @var string
     *
     * @ORM\Column(name="length", type="decimal", precision=5, scale=2)
     */
    private $length;

    /**
     * @var string
     *
     * @ORM\Column(name="height", type="decimal", precision=5, scale=2)
     */
    private $height;


    /**
     * @var string
     *
     * @ORM\Column(name="parcel_width", type="decimal", precision=5, scale=2)
     */
    private $parcelWidth;

    /**
     * @var string
     *
     * @ORM\Column(name="parcel_length", type="decimal", precision=5, scale=2)
     */
    private $parcelLength;

    /**
     * @var string
     *
     * @ORM\Column(name="parcel_height", type="decimal", precision=5, scale=2)
     */
    private $parcelHeight;

    /**
     * @var string
     *
     * @ORM\Column(name="parcel_type", type="string", length=50)
     */
    private $parcelType;

    /**
     * @var string
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity = 1;

    /**
     * @var string
     *
     * @ORM\Column(name="emc", type="boolean")
     */
    private $emc = false;

    /**
     * @var string
     *
     * @ORM\Column(name="crawl_ref", type="string", length=50, nullable=true)
     */
    private $crawlRef;

    /**
     * @var string
     *
     * @ORM\Column(name="crawl_uq_ref", type="string", length=255, nullable=true)
     */
    private $crawlUqRef;

    public function __construct() {
        $this->images = new ArrayCollection();
        $this->attributesValues = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->collections = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->proposals = new ArrayCollection();
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
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setProduct($this);
        }

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
        $image->setProduct(null);
    }

    /**
     * Get images
     *
     * @return ArrayCollection
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

        $attributeValue->setProduct($this);

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

    /**
     * Add favorite
     *
     * @param \ProductBundle\Entity\Favorite $favorite
     *
     * @return Product
     */
    public function addFavorite(\ProductBundle\Entity\Favorite $favorite)
    {
        $this->favorites[] = $favorite;

        return $this;
    }

    /**
     * Remove favorite
     *
     * @param \ProductBundle\Entity\Favorite $favorite
     */
    public function removeFavorite(\ProductBundle\Entity\Favorite $favorite)
    {
        $this->favorites->removeElement($favorite);
    }

    /**
     * Get favorites
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFavorites()
    {
        return $this->favorites;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Product
     */
    public function setUser(\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
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
    }

    /**
     * Remove collection
     * @param Collection $collection
     * @return Product
     */
    public function removeCollection(Collection $collection)
    {
        $this->collections->removeElement($collection);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getThreads()
    {
        return $this->threads;
    }

    /**
     * @param mixed $threads
     */
    public function setThreads($threads)
    {
        $this->threads = $threads;
    }

    /**
     * Add thread
     *
     * @param \MessageBundle\Entity\Thread $thread
     *
     * @return Product
     */
    public function addThread(\MessageBundle\Entity\Thread $thread)
    {
        $this->threads[] = $thread;

        return $this;
    }

    /**
     * Remove thread
     *
     * @param \MessageBundle\Entity\Thread $thread
     */
    public function removeThread(\MessageBundle\Entity\Thread $thread)
    {
        $this->threads->removeElement($thread);
    }

    /**
     * Add delivery
     *
     * @param \OrderBundle\Entity\Delivery $delivery
     *
     * @return Product
     */
    public function addDelivery(\OrderBundle\Entity\Delivery $delivery)
    {
        $delivery->setProduct($this);
        $this->deliveries[] = $delivery;
    }

    /*
     * Set originalPrice
     *
     * @param string $originalPrice
     *
     * @return Product
     */
    public function setOriginalPrice($originalPrice)
    {
        $this->originalPrice = $originalPrice;
        return $this;
    }

    /**
     * Remove delivery
     *
     * @param \OrderBundle\Entity\Delivery $delivery
     */
    public function removeDelivery(\OrderBundle\Entity\Delivery $delivery)
    {
        $this->deliveries->removeElement($delivery);
    }

    /**
     * Get deliveries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDeliveries()
    {
        return $this->deliveries;
    }

    /**
     * Add order
     *
     * @param \OrderBundle\Entity\Order $order
     *
     * @return Product
     */
    public function addOrder(\OrderBundle\Entity\Order $order)
    {
        $this->orders[] = $order;
    }

    /*
     * Get originalPrice
     *
     * @return string
     */
    public function getOriginalPrice()
    {
        return $this->originalPrice;
    }

    /**
     * Set allowOffer
     *
     * @param boolean $allowOffer
     *
     * @return Product
     */
    public function setAllowOffer($allowOffer)
    {
        $this->allowOffer = $allowOffer;

        return $this;
    }

    /**
     * Remove order
     *
     * @param \OrderBundle\Entity\Order $order
     */
    public function removeOrder(\OrderBundle\Entity\Order $order)
    {
        $this->orders->removeElement($order);
    }

    /**
     * Get orders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrders()
    {
        return $this->orders;
    }


    /*
     * Get allowOffer
     *
     * @return boolean
     */
    public function getAllowOffer()
    {
        return $this->allowOffer;
    }

    /**
     * Set address
     *
     * @param \LocationBundle\Entity\Address $address
     *
     * @return Product
     */
    public function setAddress(\LocationBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \LocationBundle\Entity\Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     *
     * @return Product
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return integer
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return ArrayCollection
     */
    public function getProposals()
    {
        return $this->proposals;
    }

    /**
     * @param ArrayCollection $proposals
     * @return Product
     */
    public function setProposals($proposals)
    {
        $this->proposals = $proposals;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProposalAccepted()
    {
        return $this->proposalAccepted;
    }

    /**
     * @param mixed $proposalAccepted
     */
    public function setProposalAccepted($proposalAccepted)
    {
        $this->proposalAccepted = $proposalAccepted;
    }

    /**
     * @return string
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param string $height
     * @return Product
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return string
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param string $width
     * @return Product
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return string
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param string $length
     * @return Product
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Add proposal
     *
     * @param \OrderBundle\Entity\OrderProposal $proposal
     *
     * @return Product
     */
    public function addProposal(\OrderBundle\Entity\OrderProposal $proposal)
    {
        $this->proposals[] = $proposal;

        return $this;
    }

    /**
     * Remove proposal
     *
     * @param \OrderBundle\Entity\OrderProposal $proposal
     */
    public function removeProposal(\OrderBundle\Entity\OrderProposal $proposal)
    {
        $this->proposals->removeElement($proposal);
    }

    /**
     * @return string
     */
    public function getEmc()
    {
        return $this->emc;
    }

    /**
     * @param string $emc
     */
    public function setEmc($emc)
    {
        $this->emc = $emc;
    }

    /**
     * @return string
     */
    public function getParcelWidth()
    {
        if (empty($this->parcelWidth) || $this->parcelWidth == 0) {
            return $this->width * 100;
        }

        return $this->parcelWidth;
    }

    /**
     * @param string $parcelWidth
     */
    public function setParcelWidth($parcelWidth)
    {
        $this->parcelWidth = $parcelWidth;
    }

    /**
     * @return string
     */
    public function getParcelLength()
    {
        if (empty($this->parcelLength) || $this->parcelLength == 0) {
            return $this->length * 100;
        }

        return $this->parcelLength;
    }

    /**
     * @param string $parcelLength
     */
    public function setParcelLength($parcelLength)
    {
        $this->parcelLength = $parcelLength;
    }

    /**
     * @return string
     */
    public function getParcelHeight()
    {
        if (empty($this->parcelHeight)  || $this->parcelHeight == 0) {
            return $this->height * 100;
        }

        return $this->parcelHeight;
    }

    /**
     * @param string $parcelHeight
     */
    public function setParcelHeight($parcelHeight)
    {
        $this->parcelHeight = $parcelHeight;
    }

    /**
     * @return string
     */
    public function getParcelType()
    {

        if (empty($this->parcelType)) {
            return 'box';
        }

        return $this->parcelType;
    }

    /**
     * @param string $parcelType
     */
    public function setParcelType($parcelType)
    {
        $this->parcelType = $parcelType;
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        if (empty($this->quantity)) {
            return 1;
        }

        return $this->quantity;
    }

    /**
     * @param string $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getLocation() {
        return $this->getAddress()->getGeoLatitude() . ',' . $this->getAddress()->getGeoLongitude();
    }

    /**
     * @return string
     */
    public function getCrawlRef()
    {
        return $this->crawlRef;
    }

    /**
     * @param string $crawlRef
     */
    public function setCrawlRef($crawlRef)
    {
        $this->crawlRef = $crawlRef;

        return $this;
    }

    /**
     * @return string
     */
    public function getCrawlUqRef()
    {
        return $this->crawlUqRef;
    }

    /**
     * @param string $crawlUqRef
     */
    public function setCrawlUqRef($crawlUqRef)
    {
        $this->crawlUqRef = $crawlUqRef;

        return $this;
    }
}
