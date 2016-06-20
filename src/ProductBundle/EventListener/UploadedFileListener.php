<?php

namespace ProductBundle\EventListener;

use Symfony\Component\HttpKernel\HttpKernel;
use Vich\UploaderBundle\Event\Event;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\File as File;
use Symfony\Component\HttpFoundation\File\UploadedFile as UploadedFile;

class UploadedFileListener
{

    public function onPreUpload(Event $event)
    {
        $mapping  = $event->getMapping();
        // Convert images to jpg only for product images
        if ($mapping->getMappingName() == 'product_images') {
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
            $imageFile = new UploadedFile($realPath, uniqid().'.jpg');
            $uploadedFile->setImageFile($imageFile);
        }
    }
}