<?php

namespace LocationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * County
 *
 * @ORM\Table(name="county")
 * @ORM\Entity(repositoryClass="LocationBundle\Repository\CountyRepository")
 */
class County
{
    use ORMBehaviors\Sluggable\Sluggable;
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
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;
    /**
     * @ORM\OneToMany(targetEntity="City", mappedBy="county")
     */
    private $cities;

    /**
     * @return mixed
     */
    public function getCities()
    {
        return $this->cities;
    }

    /**
     * @param mixed $cities
     * @return County
     */
    public function setCities($cities)
    {
        $this->cities = $cities;

        return $this;
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
     * @return County
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
     * Set code
     *
     * @param string $code
     *
     * @return County
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

    public function getSluggableFields()
    {
        return [ 'name' ];
    }

}
