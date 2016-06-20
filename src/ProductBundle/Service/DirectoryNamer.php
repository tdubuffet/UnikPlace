<?php

namespace ProductBundle\Service;

use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;


class DirectoryNamer implements DirectoryNamerInterface
{

    public function directoryName($object, PropertyMapping $mapping)
    {
        $path = md5($object->getImage());
        return substr($path, 0, 5);
    }

}