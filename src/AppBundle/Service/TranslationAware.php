<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 23/11/16
 * Time: 16:09
 */

namespace AppBundle\Service;


use Symfony\Component\HttpFoundation\Session\Session;

trait TranslationAware
{

    public function translation($text)
    {

        $result = Translation::doTranslate($text, $this->getLocale());

        if ($result['from'] == $result['to']) {
            return $text;
        }

        return $result['translationText'];
    }

    public function getLocale()
    {
        return (new Session())->get('_locale', 'fr');
    }

}