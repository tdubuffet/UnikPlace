<?php

namespace AppBundle\Command;

use A2lix\TranslationFormBundle\Tests\Gedmo\Fixtures\Entity\Product;
use OrderBundle\Event\OrderEvent;
use OrderBundle\Event\OrderEvents;
use ProductBundle\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\HttpFoundation\File\File;

class MesDecouvertesCommand extends ContainerAwareCommand
{

    use CrawlerCommand;

    private $url = 'http://mes-decouvertes.com/shop/';
    protected $crawlRef = 'mes-decouvertes';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName("crawl:decouvertes")
            ->setDescription("Crawler de merde - Mes decouvertes")
            ->addArgument('username', InputArgument::REQUIRED, 'Quel est l\'utilisateur d\'import ?');
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
        $username = $input->getArgument('username');
        $this->setUsername($username, $output);

        $output->writeln('-----------------------------------------------------------');
        $output->writeln('-------------------------  START --------------------------');
        $output->writeln('-----------------------------------------------------------');
        $html = file_get_contents($this->url);


        $crawler = new Crawler($html);

        $crawler->filter('ul.product-categories > li')->each(function (Crawler $node, $i) use ($output) {
            $catUrl = $node->filter('a')->first()->attr('href');
            $titre = $node->filter('a')->first()->text();

            $output->writeln('[' . $this->crawlRef . '] - Catégorie: ' . $titre . ' - ' . $catUrl . '');

            $this->findProductsCategory($catUrl, $output, $titre);
        });


        $this->outMessage($output);

    }

    private function findProductsCategory($href, $output, $categoryName)
    {

        $html = file_get_contents($href);

        $crawler = new Crawler($html);

        if ($crawler->filter('a.product-name')->count() > 0) {
            $nextUrl = $crawler->filter('.product-wrapper .product-listing a.product-name')->each(function (Crawler $node, $i) use ($output, $categoryName) {
                $product = $node->attr('href');
                $titre = $node->text();

                $output->writeln('[' . $this->crawlRef . '][PRODUIT] - - ' . $titre);

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
            $output->writeln('[' . $this->crawlRef . '][PRODUIT] - [' . $href . '] - IGNORE - INCOMPLETE');
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

        $product['crawlRef'] = $this->crawlRef;
        $p = $doctrine->getRepository('ProductBundle:Product')->findOneBy([
            'crawlRef' => $this->crawlRef,
            'crawlUqRef' => $product['sku'],
        ]);


        $this->totalProduct++;

        if ($p) {

            $output->writeln('[' . $this->crawlRef . '][PRODUIT] - [' . $product['sku'] . '] - IGNORE - DOUBLON => ' . $product['title']);
            $this->totalIgnore++;
        } else {
            $output->writeln('[' . $this->crawlRef . '] - [PRODUIT] - [' . $product['sku'] . '] - AJOUT - ' . $product['title']);

            $return = $this->saveProduct($product);

            if ($return === true) {
                $output->writeln('[' . $this->crawlRef . '] - [PRODUIT] - [' . $product['sku'] . '] - AJOUT - ' . $product['title'] . ' => OK');
                $this->totalInsert++;
            } else {
                $output->writeln('[' . $this->crawlRef . '] - [PRODUIT] - [' . $product['sku'] . '] - ERROR - ' . $product['title'] . ' => ' . $return);
                $this->totalError++;
            }
        }

    }
}