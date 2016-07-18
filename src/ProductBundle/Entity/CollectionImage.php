<?php

namespace ProductBundle\Entity;

use ProductBundle\Service as Service;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\PropertyMapping;


/**
 * Image
 *
 * @ORM\Table(name="collection_image")
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\ImageRepository")
 * @Vich\Uploadable
 */
class CollectionImage
{
    use ORMBehaviors\Timestampable\Timestampable;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="collection_images", fileNameProperty="image")
     * @Assert\Image(maxSize="2M", mimeTypes={"image/jpeg", "image/jpg", "image/png", "image/gif"})
     * @var File
     */
    private $imageFile;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($image) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime();
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set product
     *
     * @param Collection $collection
     *
     * @return CollectionImage
     */
    public function setCollection(Collection $collection = null)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Get product
     *
     * @return Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    public function getImagePath()
    {
        $directoryNamer = new Service\DirectoryNamer();
        $mapping = new PropertyMapping('', '');

        return $directoryNamer->directoryName($this, $mapping).'/'.$this->getImage();
    }

    public function __toString() {
        return (string) $this->id;
    }
}
