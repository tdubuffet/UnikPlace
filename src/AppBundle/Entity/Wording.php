<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * Translation
 *
 * @ORM\Table(name="wording")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\WordingRepository")
 */
class Wording
{

    use ORMBehaviors\Translatable\Translatable;

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
     * @var bool
     *
     * @ORM\Column(name="is_html", type="boolean")
     */
    private $isHtml = false;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TranslationPage")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id")
     */
    private $page;


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
     * @return Translation
     */
    public function setCode($code)
    {

        $code = $this->generateCode($code);

        $parentCode = $this->getPage()->getCode();

        $this->code = $parentCode . '.' . $code;

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
     * Set isHtml
     *
     * @param boolean $isHtml
     *
     * @return Translation
     */
    public function setIsHtml($isHtml)
    {
        $this->isHtml = $isHtml;

        return $this;
    }

    /**
     * Get isHtml
     *
     * @return bool
     */
    public function getIsHtml()
    {
        return $this->isHtml;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Translation
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

    public function generateCode($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return false;
        }

        return $text;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }
}

