<?php

namespace LocationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * Address
 *
 * @ORM\Table(name="address")
 * @ORM\Entity(repositoryClass="LocationBundle\Repository\AddressRepository")
 */
class Address
{
    use ORMBehaviors\SoftDeletable\SoftDeletable;

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
     * @ORM\Column(name="civility", type="string", length=3)
     */
    private $civility = 'mr';

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=255)
     */
    private $street;

    /**
     * @var string
     *
     * @ORM\Column(name="additional", type="string", length=255, nullable=true)
     */
    private $additional;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="addresses")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;


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
     * @return string
     */
    public function getLastname()
    {
        if (empty($this->lastname)) {
            $this->lastname = $this->getUser()->getLastname();
            $this->firstname = $this->getUser()->getFirstname();
            //@todo Fix empty value - Update delivery
        }

        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        if (empty($this->firstname)) {
            $this->lastname = $this->getUser()->getLastname();
            $this->firstname = $this->getUser()->getFirstname();
            //@todo Fix empty value - Update delivery
        }

        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return Address
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set city
     *
     * @param \LocationBundle\Entity\City $city
     *
     * @return Address
     */
    public function setCity(\LocationBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \LocationBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Address
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

    public function __toString() {
        return $this->firstname.' ' . $this->lastname . ' - '.$this->street.(!is_null($this->additional) ? '- '.$this->additional : '').' ('.$this->getCity()->getZipcode().' '.$this->getCity()->getName().')';
    }

    /**
     * Set additional
     *
     * @param string $additional
     *
     * @return Address
     */
    public function setAdditional($additional)
    {
        $this->additional = $additional;

        return $this;
    }

    /**
     * Get additional
     *
     * @return string
     */
    public function getAdditional()
    {
        return $this->additional;
    }

    /**
     * @return string
     */
    public function getCivility()
    {
        if (empty($this->civility)) {
            $this->civility = 'mr';
        }

        return $this->civility;
    }

    /**
     * @param string $civility
     */
    public function setCivility(string $civility)
    {
        $this->civility = $civility;
    }
}
