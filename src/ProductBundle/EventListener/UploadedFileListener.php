<?php

namespace ProductBundle\EventListener;

use Vich\UploaderBundle\Event\Event;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile as UploadedFile;

class UploadedFileListener
{
    private $env;

    public function __construct($env)
    {
        $this->env = $env;
    }

    public function onPreUpload(Event $event)
    {
        $mapping  = $event->getMapping();
        // Convert images to jpg only for product images
        if ($mapping->getMappingName() == 'product_images' || $mapping->getMappingName() == 'collection_images') {
            // Get uploaded file
            $uploadedFile = $event->getObject();
            $imageFile = $uploadedFile->getImageFile();
            $realPath = $imageFile->getRealPath();

            // Convert uploaded temporary file
            $manager = new ImageManager();
            $image = $manager->make($realPath);
            $newPath = $realPath.'.jpg';
            $image->save($newPath);
            rename($newPath, $realPath);

            // Create the uploaded file
            if ($this->env == "dev") {
                $imageFile = new UploadedFile($realPath, uniqid().'.jpg', null, null, null, true);
            }else {
                $imageFile = new UploadedFile($realPath, uniqid().'.jpg');
            }

            $uploadedFile->setImageFile($imageFile);
        }
    }
}