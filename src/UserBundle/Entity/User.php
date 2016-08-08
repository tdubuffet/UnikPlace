<?php

namespace UserBundle\Entity;

use FOS\MessageBundle\Model\ParticipantInterface;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser implements ParticipantInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        $this->favorites = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    /**
     * @var string
     * @Assert\Email(message="L'email {{ value }} n'est pas valide", checkMX=true, groups={"Registration"})
     * @Assert\Length(min=3, max=100, minMessage="Veuillez saisir au moins {{ limit }} caractères", maxMessage="Veuillez saisir au maximum {{ limit }} caractères", groups={"Registration"})
     */
    protected $email;

    /** @ORM\Column(name="facebook_id", type="string", length=255, nullable=true) */
    protected $facebookId;

    /** @ORM\Column(name="facebook_access_token", type="string", length=255, nullable=true) */
    protected $facebookAccessToken;

    /** @ORM\Column(name="google_id", type="string", length=255, nullable=true) */
    protected $googleId;

    /** @ORM\Column(name="google_access_token", type="string", length=255, nullable=true) */
    protected $googleAccessToken;

    /** @ORM\Column(name="twitter_id", type="string", length=255, nullable=true) */
    protected $twitterId;

    /** @ORM\Column(name="twitter_access_token", type="string", length=255, nullable=true) */
    protected $twitterAccessToken;

    /**
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Veuillez fournir un prénom", groups={"Registration", "Profile"})
     * @Assert\Length(min=3, max=75, minMessage="Veuillez saisir au moins {{ limit }} caractères", maxMessage="Veuillez saisir au maximum {{ limit }} caractères", groups={"Registration"})
     */
    protected $firstname;

    /**
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Veuillez fournir un nom", groups={"Registration", "Profile"})
     * @Assert\Length(min=3, max=75, minMessage="Veuillez saisir au moins {{ limit }} caractères", maxMessage="Veuillez saisir au maximum {{ limit }} caractères", groups={"Registration"})
     */
    protected $lastname;

    /**
     * @var date $birthday
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    protected $birthday;

    /**
     * @ORM\Column(name="nationality", type="string", length=2)
     * @Assert\Country()
     */
    protected $nationality;

    /**
     * @ORM\Column(name="residential_country", type="string", length=2)
     * @Assert\Country()
     */
    protected $residentialCountry;

    /**
     * @ORM\Column(name="phone", type="string", nullable=true, length=20)
     */
    protected $phone;

    /**
     * @var bool
     *
     * @ORM\Column(name="pro", type="boolean")
     */
    protected $pro;

    /**
     * @ORM\Column(name="company_code", type="string", length=255, nullable=true)
     */
    protected $companyCode;

    /**
     * @ORM\Column(name="company_name", type="string", length=255, nullable=true)
     */
    protected $companyName;

    /**
     * @ORM\Column(name="company_address", type="string", length=255, nullable=true)
     */
    protected $companyAddress;

    /**
     * @ORM\Column(name="company_zipcode", type="string", length=255, nullable=true)
     */
    protected $companyZipcode;

    /**
     * @ORM\Column(name="company_city", type="string", length=255, nullable=true)
     */
    protected $companyCity;


    /**
     * @ORM\Column(name="newsletter", type="boolean")
     */
    protected $newsletter = true;

    /**
     * @ORM\Column(name="email_classified", type="boolean")
     */
    protected $emailClassified = true;

    /**
     * @ORM\OneToMany(targetEntity="ProductBundle\Entity\Favorite", mappedBy="user")
     */
    private $favorites;

    /**
     * @ORM\OneToMany(targetEntity="ProductBundle\Entity\Product", mappedBy="user")
     */
    private $products;

    /**
     * @ORM\OneToMany(targetEntity="OrderBundle\Entity\OrderProposal", mappedBy="user")
     */
    private $proposals;

    /**
     * @ORM\OneToMany(targetEntity="LocationBundle\Entity\Address", mappedBy="user")
     */
    private $addresses;

    /** @ORM\Column(name="mangopay_user_id", type="string", length=255, nullable=true) */
    protected $mangopayUserId;

    /** @ORM\Column(name="mangopay_blocked_wallet_id", type="string", length=255, nullable=true) */
    protected $mangopayBlockedWalletId;

    /** @ORM\Column(name="mangopay_free_wallet_id", type="string", length=255, nullable=true) */
    protected $mangopayFreeWalletId;

    /**
     * @ORM\OneToMany(targetEntity="OrderBundle\Entity\Order", mappedBy="user")
     */
    private $orders;


    public function setEmail($email)
    {
        $email = is_null($email) ? '' : $email;
        parent::setEmail($email);
        $this->setUsername($email);

        return $this;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    public function getExpiresAt()
    {
        return $this->expiresAt;
    }


    public function getCredentialsExpireAt()
    {
        return $this->credentialsExpireAt;
    }

    /**
     * Add favorite
     *
     * @param \ProductBundle\Entity\Favorite $favorite
     *
     * @return User
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
     * Add product
     *
     * @param \ProductBundle\Entity\Product $product
     *
     * @return User
     */
    public function addProduct(\ProductBundle\Entity\Product $product)
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * Remove product
     *
     * @param \ProductBundle\Entity\Product $product
     */
    public function removeProduct(\ProductBundle\Entity\Product $product)
    {
        $this->products->removeElement($product);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     *
     * @return User
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set nationality
     *
     * @param string $nationality
     *
     * @return User
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;

        return $this;
    }

    /**
     * Get nationality
     *
     * @return string
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * Set pro
     *
     * @param boolean $pro
     *
     * @return User
     */
    public function setPro($pro)
    {
        $this->pro = $pro;

        return $this;
    }

    /**
     * Get pro
     *
     * @return boolean
     */
    public function getPro()
    {
        return $this->pro;
    }

    /**
     * Add address
     *
     * @param \LocationBundle\Entity\Address $address
     *
     * @return User
     */
    public function addAddress(\LocationBundle\Entity\Address $address)
    {
        $this->addresses[] = $address;

        return $this;
    }

    /**
     * Remove address
     *
     * @param \LocationBundle\Entity\Address $address
     */
    public function removeAddress(\LocationBundle\Entity\Address $address)
    {
        $this->addresses->removeElement($address);
    }

    /**
     * Get addresses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * Add order
     *
     * @param \OrderBundle\Entity\Order $order
     *
     * @return User
     */
    public function addOrder(\OrderBundle\Entity\Order $order)
    {
        $this->orders[] = $order;

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

    /**
     * Set newsletter
     *
     * @param boolean $newsletter
     *
     * @return User
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Get newsletter
     *
     * @return boolean
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set emailClassified
     *
     * @param boolean $emailClassified
     *
     * @return User
     */
    public function setEmailClassified($emailClassified)
    {
        $this->emailClassified = $emailClassified;

        return $this;
    }

    /**
     * Get emailClassified
     *
     * @return boolean
     */
    public function getEmailClassified()
    {
        return $this->emailClassified;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return mixed
     */
    public function getProposals()
    {
        return $this->proposals;
    }

    /**
     * @param ArrayCollection $proposals
     * @return User
     */
    public function setProposals($proposals)
    {
        $this->proposals = $proposals;

        return $this;
    }


    /**
     * Set facebookId
     *
     * @param string $facebookId
     *
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    /**
     * Get facebookId
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * Set facebookAccessToken
     *
     * @param string $facebookAccessToken
     *
     * @return User
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebookAccessToken = $facebookAccessToken;

        return $this;
    }

    /**
     * Get facebookAccessToken
     *
     * @return string
     */
    public function getFacebookAccessToken()
    {
        return $this->facebookAccessToken;
    }

    /**
     * Set googleId
     *
     * @param string $googleId
     *
     * @return User
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;

        return $this;
    }

    /**
     * Get googleId
     *
     * @return string
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }

    /**
     * Set googleAccessToken
     *
     * @param string $googleAccessToken
     *
     * @return User
     */
    public function setGoogleAccessToken($googleAccessToken)
    {
        $this->googleAccessToken = $googleAccessToken;

        return $this;
    }

    /**
     * Get googleAccessToken
     *
     * @return string
     */
    public function getGoogleAccessToken()
    {
        return $this->googleAccessToken;
    }

    /**
     * Set twitterId
     *
     * @param string $twitterId
     *
     * @return User
     */
    public function setTwitterId($twitterId)
    {
        $this->twitterId = $twitterId;

        return $this;
    }

    /**
     * Get twitterId
     *
     * @return string
     */
    public function getTwitterId()
    {
        return $this->twitterId;
    }

    /**
     * Set twitterAccessToken
     *
     * @param string $twitterAccessToken
     *
     * @return User
     */
    public function setTwitterAccessToken($twitterAccessToken)
    {
        $this->twitterAccessToken = $twitterAccessToken;

        return $this;
    }

    /**
     * Get twitterAccessToken
     *
     * @return string
     */
    public function getTwitterAccessToken()
    {
        return $this->twitterAccessToken;
    }

    /**
     * Set residentialCountry
     *
     * @param string $residentialCountry
     *
     * @return User
     */
    public function setResidentialCountry($residentialCountry)
    {
        $this->residentialCountry = $residentialCountry;

        return $this;
    }

    /**
     * Get residentialCountry
     *
     * @return string
     */
    public function getResidentialCountry()
    {
        return $this->residentialCountry;
    }

    /**
     * Set companyCode
     *
     * @param string $companyCode
     *
     * @return User
     */
    public function setCompanyCode($companyCode)
    {
        $this->companyCode = $companyCode;

        return $this;
    }

    /**
     * Get companyCode
     *
     * @return string
     */
    public function getCompanyCode()
    {
        return $this->companyCode;
    }

    /**
     * Set companyName
     *
     * @param string $companyName
     *
     * @return User
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * Get companyName
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * Set companyAddress
     *
     * @param string $companyAddress
     *
     * @return User
     */
    public function setCompanyAddress($companyAddress)
    {
        $this->companyAddress = $companyAddress;

        return $this;
    }

    /**
     * Get companyAddress
     *
     * @return string
     */
    public function getCompanyAddress()
    {
        return $this->companyAddress;
    }

    /**
     * Set companyZipcode
     *
     * @param string $companyZipcode
     *
     * @return User
     */
    public function setCompanyZipcode($companyZipcode)
    {
        $this->companyZipcode = $companyZipcode;

        return $this;
    }

    /**
     * Get companyZipcode
     *
     * @return string
     */
    public function getCompanyZipcode()
    {
        return $this->companyZipcode;
    }

    /**
     * Set companyCity
     *
     * @param string $companyCity
     *
     * @return User
     */
    public function setCompanyCity($companyCity)
    {
        $this->companyCity = $companyCity;

        return $this;
    }

    /**
     * Get companyCity
     *
     * @return string
     */
    public function getCompanyCity()
    {
        return $this->companyCity;
    }

    /**
     * Set mangopayUserId
     *
     * @param string $mangopayUserId
     *
     * @return User
     */
    public function setMangopayUserId($mangopayUserId)
    {
        $this->mangopayUserId = $mangopayUserId;

        return $this;
    }

    /**
     * Get mangopayUserId
     *
     * @return string
     */
    public function getMangopayUserId()
    {
        return $this->mangopayUserId;
    }

    /**
     * Set mangopayBlockedWalletId
     *
     * @param string $mangopayBlockedWalletId
     *
     * @return User
     */
    public function setMangopayBlockedWalletId($mangopayBlockedWalletId)
    {
        $this->mangopayBlockedWalletId = $mangopayBlockedWalletId;

        return $this;
    }

    /**
     * Get mangopayBlockedWalletId
     *
     * @return string
     */
    public function getMangopayBlockedWalletId()
    {
        return $this->mangopayBlockedWalletId;
    }

    /**
     * Set mangopayFreeWalletId
     *
     * @param string $mangopayFreeWalletId
     *
     * @return User
     */
    public function setMangopayFreeWalletId($mangopayFreeWalletId)
    {
        $this->mangopayFreeWalletId = $mangopayFreeWalletId;

        return $this;
    }

    /**
     * Get mangopayFreeWalletId
     *
     * @return string
     */
    public function getMangopayFreeWalletId()
    {
        return $this->mangopayFreeWalletId;
    }

    /**
     * Add proposal
     *
     * @param \OrderBundle\Entity\OrderProposal $proposal
     *
     * @return User
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
}
