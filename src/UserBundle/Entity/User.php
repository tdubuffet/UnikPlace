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
    }

    /**
     * @var string
     * @Assert\Email(message="L'email {{ value }} n'est pas valide", checkMX=true, groups={"Registration"})
     * @Assert\Length(min=3, max=100, minMessage="Veuillez saisir au moins {{ limit }} caractères", maxMessage="Veuillez saisir au maximum {{ limit }} caractères", groups={"Registration"})
     */
    protected $email;

    /** @ORM\Column(name="facebook_id", type="string", length=255, nullable=true) */
    protected $facebook_id;

    /** @ORM\Column(name="facebook_access_token", type="string", length=255, nullable=true) */
    protected $facebook_access_token;

    /** @ORM\Column(name="google_id", type="string", length=255, nullable=true) */
    protected $google_id;

    /** @ORM\Column(name="google_access_token", type="string", length=255, nullable=true) */
    protected $google_access_token;

    /** @ORM\Column(name="twitter_id", type="string", length=255, nullable=true) */
    protected $twitter_id;

    /** @ORM\Column(name="twitter_access_token", type="string", length=255, nullable=true) */
    protected $twitter_access_token;

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
    protected $residential_country;

    /**
     * @var bool
     *
     * @ORM\Column(name="pro", type="boolean")
     */
    protected $pro;

    /**
     * @ORM\Column(name="company_code", type="string", length=255, nullable=true)
     */
    protected $company_code;

    /**
     * @ORM\Column(name="company_name", type="string", length=255, nullable=true)
     */
    protected $company_name;

    /**
     * @ORM\Column(name="company_address", type="string", length=255, nullable=true)
     */
    protected $company_address;

    /**
     * @ORM\Column(name="company_zipcode", type="string", length=255, nullable=true)
     */
    protected $company_zipcode;

    /**
     * @ORM\Column(name="company_city", type="string", length=255, nullable=true)
     */
    protected $company_city;

    /**
     * @ORM\OneToMany(targetEntity="ProductBundle\Entity\Favorite", mappedBy="user")
     */
    private $favorites;

    /**
     * @ORM\OneToMany(targetEntity="ProductBundle\Entity\Product", mappedBy="user")
     */
    private $products;

    /**
     * @ORM\OneToMany(targetEntity="LocationBundle\Entity\Address", mappedBy="user")
     */
    private $addresses;

    /** @ORM\Column(name="mangopay_user_id", type="string", length=255, nullable=true) */
    protected $mangopay_user_id;


    public function setEmail($email)
    {
        $email = is_null($email) ? '' : $email;
        parent::setEmail($email);
        $this->setUsername($email);

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
        $this->facebook_id = $facebookId;

        return $this;
    }

    /**
     * Get facebookId
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebook_id;
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
        $this->facebook_access_token = $facebookAccessToken;

        return $this;
    }

    /**
     * Get facebookAccessToken
     *
     * @return string
     */
    public function getFacebookAccessToken()
    {
        return $this->facebook_access_token;
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
        $this->google_id = $googleId;

        return $this;
    }

    /**
     * Get googleId
     *
     * @return string
     */
    public function getGoogleId()
    {
        return $this->google_id;
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
        $this->google_access_token = $googleAccessToken;

        return $this;
    }

    /**
     * Get googleAccessToken
     *
     * @return string
     */
    public function getGoogleAccessToken()
    {
        return $this->google_access_token;
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
        $this->twitter_id = $twitterId;

        return $this;
    }

    /**
     * Get twitterId
     *
     * @return string
     */
    public function getTwitterId()
    {
        return $this->twitter_id;
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
        $this->twitter_access_token = $twitterAccessToken;

        return $this;
    }

    /**
     * Get twitterAccessToken
     *
     * @return string
     */
    public function getTwitterAccessToken()
    {
        return $this->twitter_access_token;
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
     * Set residentialCountry
     *
     * @param string $residentialCountry
     *
     * @return User
     */
    public function setResidentialCountry($residentialCountry)
    {
        $this->residential_country = $residentialCountry;

        return $this;
    }

    /**
     * Get residentialCountry
     *
     * @return string
     */
    public function getResidentialCountry()
    {
        return $this->residential_country;
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
     * Set companyCode
     *
     * @param string $companyCode
     *
     * @return User
     */
    public function setCompanyCode($companyCode)
    {
        $this->company_code = $companyCode;

        return $this;
    }

    /**
     * Get companyCode
     *
     * @return string
     */
    public function getCompanyCode()
    {
        return $this->company_code;
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
        $this->company_name = $companyName;

        return $this;
    }

    /**
     * Get companyName
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->company_name;
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
        $this->company_address = $companyAddress;

        return $this;
    }

    /**
     * Get companyAddress
     *
     * @return string
     */
    public function getCompanyAddress()
    {
        return $this->company_address;
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
        $this->company_zipcode = $companyZipcode;

        return $this;
    }

    /**
     * Get companyZipcode
     *
     * @return string
     */
    public function getCompanyZipcode()
    {
        return $this->company_zipcode;
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
        $this->company_city = $companyCity;

        return $this;
    }

    /**
     * Get companyCity
     *
     * @return string
     */
    public function getCompanyCity()
    {
        return $this->company_city;
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
     * Set mangopayUserId
     *
     * @param string $mangopayUserId
     *
     * @return User
     */
    public function setMangopayUserId($mangopayUserId)
    {
        $this->mangopay_user_id = $mangopayUserId;

        return $this;
    }

    /**
     * Get mangopayUserId
     *
     * @return string
     */
    public function getMangopayUserId()
    {
        return $this->mangopay_user_id;
    }
}
