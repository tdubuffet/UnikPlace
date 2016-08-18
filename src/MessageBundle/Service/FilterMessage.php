<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 21/07/16
 * Time: 09:32
 */

namespace MessageBundle\Service;


use FOS\MessageBundle\FormType\NewThreadMessageFormType;
use ProductBundle\Entity\Product;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

class FilterMessage
{

    /**
     * Remplace les email par la chaine $replace reçue en paramètre
     *
     * @param        $string
     * @param string $replace
     * @return mixed
     */
    public static function emailReplace($string, $replace = "(email masqué)")
    {
        $pattern = '/[A-Za-z0-9\._%+-]+(\s*(arobase|@)\s*|\s*[\[|\{|\(|\s]+(at|arobase|@)[\)|\}\]|\s]+\s*)([A-Za-z0-9\.-]+(\s*\.\s*|\s*[\[|\{|\(|\s]+\(*(dot|point|\.)\s*[\)|\}|\]|\s]+))+[a-z]{2,6}/';
        return preg_replace($pattern, $replace, $string);
    }

    /**
     * Remplace les numéros de téléphone par la chaine $replace reçue en paramètre
     *
     * @param        $string
     * @param string $replace
     * @return mixed
     */
    public static function phoneReplace($string, $replace = "(téléphone masqué)")
    {
        $pattern = "/(?:1-?)?(?:\(\d{3}\)|\d{3})[-\s.]?\d{3}[-\s.]?\d{4}/x";

        return preg_replace($pattern, $replace, $string);
    }


    public static function clear($string, $replacePhone = "(téléphone masqué)", $replaceEmail = "(email masqué)")
    {
        $string = self::emailReplace($string, $replaceEmail);
        $string = self::phoneReplace($string, $replacePhone);

        return $string;
    }

}