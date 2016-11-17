<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * Translation
 *
 * @ORM\Table(name="wording_translation")
 * @ORM\Entity()
 */
class WordingTranslation
{

    use ORMBehaviors\Translatable\Translation;

    /**
     * @ORM\Column(type="text")
     */
    protected $text;

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

}

