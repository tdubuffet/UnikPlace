<?php

namespace ImageBundle\Controller;

use Intervention\Image\Constraint;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @Route("/images/{type}/{dir}/{filename}_{width}x{height}_{method}.jpg", name="productimage",
     *     defaults={"method" = "r", "type" = "products"})
     * @param $type
     * @param $dir
     * @param $filename
     * @param $width
     * @param $height
     * @param $method
     * @return Response
     */
    public function indexAction($type, $dir, $filename, $width, $height, $method)
    {
        $maxSize = 800;
        $thumbnailSize = 100;
        $quality = 90;
        $qualityThumbnail = 60;

        $manager = new ImageManager(array('driver' => 'imagick'));

        $originalFile = 'images/'.$type.'/'.$dir.'/'.$filename.'.jpg';
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

            $methodArray = [
                "r" => "resize",
                "rc" => "resizeCanvas",
                "c" => "crop",
                'f' => 'fit',
                "r2" => "resize2",
                "r3" => "resize3",
            ];
            $method = isset($methodArray[$method]) ? $methodArray[$method] : "resize";

            if ($method == "resizeCanvas") {
            }elseif ($method == "resize2") {
                $image->resize($width, $height, function (Constraint $constraint) {
                    $constraint->aspectRatio();
                });
            } elseif ($method == "resize3"){
                $image->resize($width, $height, function (Constraint $constraint) {
                    $constraint->aspectRatio();
                })->resizeCanvas($width, $height, 'center', false, 'FFFFFF');
            }else {
                $image->$method($width, $height);
            }
        }

        $headers = array('Content-Type' => 'image/jpg');

        return new Response($image->encode('jpg', $quality), 200, $headers);
    }
}
