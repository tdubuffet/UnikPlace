<?php
namespace AppBundle\Twig;

class AppExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('jsinit', array($this, 'jsInitFunction'), array(
                'is_safe' => array('html')
            )),
        );
    }

    public function jsInitFunction($files = array())
    {
        $result = "";
        array_unshift($files, 'common');
        $result .= "<script>";
        foreach ($files as $file) {
            $result .= ucfirst($file).'.init();';
        }
        $result .= "</script>";

        return $result;
    }

    public function getName()
    {
        return 'app_extension';
    }
}