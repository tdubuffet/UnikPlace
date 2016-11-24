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

class MesDecouvertesCommand extends ContainerAwareCommand
{

    private $url = 'http://mes-decouvertes.com/shop/';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName("crawl:decouvertes")
            ->setDescription("Crawler de merde - Mes decouvertes");
    }

    /**
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws LogicException When this abstract method is not implemented
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $html = file_get_contents($this->url);


        $crawler = new Crawler($html);

        $crawler->filter('ul.product-categories > li')->each(function (Crawler $node, $i) use ($output) {
            $catUrl = $node->filter('a')->first()->attr('href');
            $titre = $node->filter('a')->first()->text();

            $output->writeln('Catégorie: ' . $titre . ' - ' . $catUrl . '');

            $this->findProductsCategory($catUrl, $output, $titre);
        });

    }

    private function findProductsCategory($href, $output, $categoryName)
    {

        $html = file_get_contents($href);

        $crawler = new Crawler($html);

        if ($crawler->filter('a.product-name')->count() > 0) {
            $nextUrl = $crawler->filter('.product-wrapper .product-listing a.product-name')->each(function (Crawler $node, $i) use ($output, $categoryName) {
                $product = $node->attr('href');
                $titre = $node->text();

                $output->writeln('[PRODUIT] - - ' . $titre);

                $this->loadProduct($product, $categoryName, $output);
            });
        }


        if ($crawler->filter('a.page-numbers.next')->count() > 0) {
            $nextUrl = $crawler->filter('a.page-numbers.next')->attr('href');

            $this->findProductsCategory($nextUrl, $output, $categoryName);
        }

    }

    public function loadProduct($href, $categoryName, $output)
    {
        $html = file_get_contents($href);

        $crawler = new Crawler($html);

        if (
            $crawler->filter('h1[itemprop="name"]')->count() == 0 ||
            $crawler->filter('span[itemprop="sku"]')->count() == 0 ||
            $crawler->filter('meta[itemprop="price"]')->count() == 0
        ) {
            $output->writeln('[PRODUIT] - [' . $href . '] - IGNORE - INCOMPLETE');
            return null;
        }

        $product = [
            'title' => $crawler->filter('h1[itemprop="name"]')->first()->text(),
            'sku' => $crawler->filter('span[itemprop="sku"]')->first()->text(),
            'price' => $crawler->filter('meta[itemprop="price"]')->first()->attr('content'),
            'images' => []
        ];

        if ($crawler->filter('#tab-description')->count() > 0) {
            $product['description'] = str_replace('Description du produit', '', $crawler->filter('#tab-description')->first()->text());
        } else {
            $product['description'] = '';
        }

        if ($crawler->filter('.thumbnail-image > a[itemprop="image"]')->count() > 0) {
            $crawler->filter('.thumbnail-image > a[itemprop="image"]')->each(function (Crawler $node, $i) use (&$product) {
                $product['images'][] = $this->uploadImage($node->attr('href'));
            });
        }

        $doctrine = $this->getContainer()->get('doctrine');

        $p = $doctrine->getRepository('ProductBundle:Product')->findOneBy([
            'crawlRef' => 'mes-decouvertes',
            'crawlUqRef' => $product['sku'],
        ]);

        if ($p) {

            $output->writeln('[PRODUIT] - [' . $product['sku'] . '] - IGNORE - DOUBLON => ' . $product['title']);
            $this->saveProduct($product);
        } else {
            $output->writeln('[PRODUIT] - [' . $product['sku'] . '] - AJOUT - ' . $product['title']);
            $this->saveProduct($product);
            $output->writeln('[PRODUIT] - [' . $product['sku'] . '] - AJOUT - ' . $product['title'] . ' => OK');
        }

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

        $user = $doctrine->getRepository('UserBundle:User')->findOneBy(['username' => 'dubuffet.thibault@gmail.com']);

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
            ->setCrawlRef('mes-decouvertes')
            ->setCrawlUqRef($data['sku']);


        $currency = $doctrine
            ->getRepository('ProductBundle:Currency')
            ->findOneBy(['code' => 'EUR']);
        if ($currency) {
            $product->setCurrency($currency);
        }

        $category = $doctrine
            ->getRepository('ProductBundle:Category')
            ->findOneBy(['id' => 1]);

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


        $product->setParcelHeight(0);
        $product->setParcelWidth(0);
        $product->setParcelLength(0);
        $product->setParcelType(0);

        $doctrine->getManager()->persist($product);
        $doctrine->getManager()->flush();

        foreach ($data['images'] as $file) {

            $file->setProduct($product);


            $doctrine->getManager()->persist($file);
            $doctrine->getManager()->flush();

        }

    }


}