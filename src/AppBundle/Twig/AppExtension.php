<?php
namespace AppBundle\Twig;

use ProductBundle\Entity\CollectionImage;
use ProductBundle\Entity\Image;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AppExtension extends \Twig_Extension
{
    private $generator;

    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function getFunctions()
    {
        return array(
            'jsinit' => new \Twig_SimpleFunction('jsinit', array($this, 'jsInitFunction'), array('is_safe' => array('html'))),
            'loadpic' => new \Twig_SimpleFunction('loadpic', array($this, 'loadPicFunction'), array('is_safe' => array('html'))),
        );
    }

    public function jsInitFunction($files = array())
    {
        $result = "";
        array_unshift($files, 'common');
        $result .= "<script>$(document).ready(function() {";
        foreach ($files as $file) {
            $result .= ucfirst($file).'.init();';
        }
        $result .= "});</script>";

        return $result;
    }

    /**
     * @param Image|CollectionImage $image
     * @param int $width
     * @param int $height
     * @param string $method
     * @param string $type
     * @return string
     */
    public function loadPicFunction($image, $width, $height, $method = "r", $type = "products")
    {
        if (!$image instanceof Image && !$image instanceof CollectionImage) {
            throw new NotFoundHttpException('Image Not Found');
        }

        $path = $image->getImagePath();

        $res = preg_match('#^([-/0-9a-f]*)/([-/0-9a-f]*)(.jpg)#', $path, $matches);
        if ($res != 0) {
            $path = $this->generator->generate('productimage', array(
                'dir' => $matches[1],
                'filename' => $matches[2],
                'width' => $width,
                'height' => $height,
                'method' => $method,
                'type' => $type
            ));
        }

        return $path;
    }

    public function getName()
    {
        return 'app_extension';
    }
}