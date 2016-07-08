<?php

namespace ImageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @Route("/images/product/{dir}/{filename}_{width}x{height}.jpg", name="productimage")
     */
    public function indexAction($dir, $filename, $width, $height)
    {
        $maxSize            = 800;
        $thumbnailSize      = 100;
        $quality            = 90;
        $qualityThumbnail   = 60;

        $manager = new ImageManager(array('driver' => 'imagick'));

        $originalFile = 'images/products/'.$dir.'/'.$filename.'.jpg';
        try {
            $image = $manager->make($originalFile);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Image Not Found');
        }

        if (!empty($width) && !empty($height)) {
            $width = ($width > $maxSize) ? $maxSize : $width;
            $height = ($height > $maxSize) ? $maxSize : $height;

            if ($width < $thumbnailSize || $height < $thumbnailSize) {
                $quality = $qualityThumbnail;
            }

            $image->resize($width, $height);
        }

        $headers = array('Content-Type' => 'image/jpg');
        return new Response($image->encode('jpg', $quality), 200, $headers);
    }
}
