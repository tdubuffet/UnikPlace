<?php

namespace AppBundle\Command;

use A2lix\TranslationFormBundle\Tests\Gedmo\Fixtures\Entity\Product;
use OrderBundle\Event\OrderEvent;
use OrderBundle\Event\OrderEvents;
use ProductBundle\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\HttpFoundation\File\File;

abstract class CrawlerCommand extends ContainerAwareCommand
{


    public $totalProduct = 0;
    public $totalInsert = 0;
    public $totalIgnore = 0;
    public $totalError = 0;
    public $start = 0;

    private $username;

    public function setUsername($username, $output)
    {
        $this->start = new \DateTime();

        $output->writeln('[' . $this->crawlRef . '][Import] - ' . $username);

    }

    public function outMessage($output)
    {
        $end = new \DateTime();
        $output->writeln('Début ::: [' . $this->start->format('d/m/Y H:i:s') . ']');
        $output->writeln('Fin ::: [' . $end->format('d/m/Y H:i:s') . ']');
        $output->writeln('Total product ::: [' . $this->totalProduct . ']');
        $output->writeln('Total product insert ::: [' . $this->totalInsert . ']');
        $output->writeln('Total produit ignore ::: [' . $this->totalIgnore . ']');
        $output->writeln('Total produit error ::: [' . $this->totalError . ']');
        $output->writeln('-----------------------------------------------------------');
        $output->writeln('--------------------------  END ---------------------------');
        $output->writeln('-----------------------------------------------------------');
    }

    public function uploadImage($href)
    {

        $pathinfo = pathinfo($href);

        $image = new Image();
        $image->setImage(md5($pathinfo['filename']) . '.' . $pathinfo['extension']);


        $doctrine = $this->getContainer()->get('doctrine');
        $doctrine->getManager()->persist($image);
        $doctrine->getManager()->flush();

        $tmpfname = __DIR__ . '/../../../web/images/products/' . $image->getImagePath();

        if (!is_dir(dirname($tmpfname))) {
            mkdir(dirname($tmpfname));
        }

        $img = file_get_contents($href);
        file_put_contents($tmpfname, $img);

        return $image;

    }

    public function saveProduct($data)
    {

        $doctrine = $this->getContainer()->get('doctrine');

        $user = $doctrine->getRepository('UserBundle:User')->findOneBy(['username' => $this->username]);

        if (!$user) {
            return 'Utilisateur inconnu';
        }

        $product = new \ProductBundle\Entity\Product();
        $product
            ->setName($data['title'])
            ->setDescription($data['description'])
            ->setPrice($data['price'])
            ->setAllowOffer(false)
            ->setWeight(0)
            ->setLength(0)
            ->setWidth(0)
            ->setQuantity(1)
            ->setHeight(0)
            ->setUser($user)
            ->setCrawlRef($data['crawlRef'])
            ->setCrawlUqRef($data['sku']);


        $product->setParcelHeight(0);
        $product->setParcelWidth(0);
        $product->setParcelLength(0);
        $product->setParcelType(0);

        if (isset($data['width'])) {
            $product->setWidth($data['width']);
            $product->setParcelWidth($data['width']);
        }

        if (isset($data['length'])) {
            $product->setLength($data['length']);
            $product->setParcelLength($data['length']);
        }

        if (isset($data['height'])) {
            $product->setHeight($data['height']);
            $product->setParcelHeight($data['height']);
        }


        $currency = $doctrine
            ->getRepository('ProductBundle:Currency')
            ->findOneBy(['code' => 'EUR']);
        if ($currency) {
            $product->setCurrency($currency);
        }


        if (isset($data['category'])) {
            $category = $doctrine
                ->getRepository('ProductBundle:Category')
                ->findOneBy(['id' => $data['category']]);
        }

        if (!$category) {
            $category = $doctrine
                ->getRepository('ProductBundle:Category')
                ->findOneBy(['id' => 1]);
        }

        if ($category) {
            $product->setCategory($category);
        }

        $status = $doctrine
            ->getRepository('ProductBundle:Status')
            ->findOneBy(['name' => 'awaiting']);

        if ($status) {
            $product->setStatus($status);
        }

        $address = $doctrine
            ->getRepository('LocationBundle:Address')
            ->findOneBy(['user' => $user]);

        if ($address) {
            $product->setAddress($address);
        }

        $doctrine->getManager()->persist($product);
        $doctrine->getManager()->flush();

        foreach ($data['images'] as $file) {

            $file->setProduct($product);


            $doctrine->getManager()->persist($file);
            $doctrine->getManager()->flush();

        }

        return true;
    }


}