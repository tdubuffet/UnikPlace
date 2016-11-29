<?php

namespace AppBundle\Command;

use A2lix\TranslationFormBundle\Tests\Gedmo\Fixtures\Entity\Product;
use OrderBundle\Event\OrderEvent;
use OrderBundle\Event\OrderEvents;
use ProductBundle\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\HttpFoundation\File\File;

class CariporelCommand extends CrawlerCommand
{

    protected $crawlRef = 'cariporel';

    private $urls = [
        '36' => [
            'http://www.cariporel.com/categorie-produit/arbre/arbre-a-bijoux/',
            'http://www.cariporel.com/categorie-produit/arbre/support-et-mobilier-naturel/',
            'http://www.cariporel.com/categorie-produit/arbre/arbre-lumineux/',
            'http://www.cariporel.com/categorie-produit/arbre/arbre-ornemental/',
            'http://www.cariporel.com/categorie-produit/mobilier/arbre-support-et-mobilier/'
        ],
        '35' => [
            'http://www.cariporel.com/categorie-produit/arbre/arbre-lumineux/',
            'http://www.cariporel.com/categorie-produit/support-bougie/bougie-solo/',
            'http://www.cariporel.com/categorie-produit/support-bougie/bougie-le-duo/',
            'http://www.cariporel.com/categorie-produit/support-bougie/bougies-poly/',
            'http://www.cariporel.com/categorie-produit/lumiere-d-ambiance/arbre-lumineux-lumiere-d-ambiance/'
        ],
        '26' => [
            'http://www.cariporel.com/categorie-produit/arbre/arbre-ornemental/',
            'http://www.cariporel.com/categorie-produit/support-floral/pose/',
            'http://www.cariporel.com/categorie-produit/support-floral/suspendu/',
            'http://www.cariporel.com/categorie-produit/support-floral/soliflor/',
            'http://www.cariporel.com/categorie-produit/support-floral/vase/',
            'http://www.cariporel.com/categorie-produit/deco/bois-mille-senteurs/',
            'http://www.cariporel.com/categorie-produit/deco/ornemental/',
        ],
        '24' => [
            'http://www.cariporel.com/categorie-produit/lumiere-d-ambiance/lampe/'
        ],
        '7' => [
            'http://www.cariporel.com/categorie-produit/mobilier/table/'
        ],
    ];


    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName("crawl:cariporel")
            ->setDescription("Crawler de merde - Cariporel")
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

        foreach ($this->urls as $key => $urls) {

            foreach ($urls as $url) {

                $crawler = new Crawler(file_get_contents($url));


                $titre = $crawler->filter('h1.page-title')->first()->text();
                $output->writeln('[' . $this->crawlRef . '] - Catégorie: ' . $titre . ' - ' . $url . '');
                $this->findProductsCategory($url, $output, $titre, $key);

            }

        }


        $this->outMessage($output);

    }

    private function findProductsCategory($href, $output, $categoryName, $keyCategory)
    {
        $html = file_get_contents($href);

        $crawler = new Crawler($html);

        if ($crawler->filter('ul.products > li > a')->count() > 0) {
            $crawler->filter('ul.products > li > a')->each(function (Crawler $node, $i) use ($output, $categoryName, $keyCategory) {
                $product = $node->attr('href');
                $titre = $node->text();

                $output->writeln('[' . $this->crawlRef . '] - [PRODUIT] - - ' . $titre);

                $this->loadProduct($product, $keyCategory, $output);
            });
        }


        if ($crawler->filter('a.page-numbers.next')->count() > 0) {
            $nextUrl = $crawler->filter('a.page-numbers.next')->attr('href');

            $this->findProductsCategory($nextUrl, $output, $categoryName, $keyCategory);
        }

    }

    public function loadProduct($href, $keyCategory, $output)
    {
        $html = file_get_contents($href);

        $crawler = new Crawler($html);

        if (
            $crawler->filter('h1[itemprop="name"]')->count() == 0 ||
            $crawler->filter('span[itemprop="sku"]')->count() == 0 ||
            $crawler->filter('meta[itemprop="price"]')->count() == 0
        ) {
            $output->writeln('[' . $this->crawlRef . '] - [PRODUIT] - [' . $href . '] - IGNORE - INCOMPLETE');
            return null;
        }

        $product = [
            'title' => trim(preg_replace('/\s\s+/', ' ', $crawler->filter('h1[itemprop="name"]')->first()->text())),
            'sku' => $crawler->filter('span[itemprop="sku"]')->first()->text(),
            'price' => $crawler->filter('meta[itemprop="price"]')->first()->attr('content'),
            'images' => []
        ];

        $product['category'] = $keyCategory;

        if ($crawler->filter('#tab-description')->count() > 0) {
            $product['description'] = str_replace('Description du produit', '', $crawler->filter('#tab-description')->first()->text());
        } else {
            $product['description'] = '';
        }

        if ($crawler->filter('img[itemprop="image"]')->count() > 0) {
            $crawler->filter('img[itemprop="image"]')->each(function (Crawler $node, $i) use (&$product) {
                $product['images'][] = $this->uploadImage($node->attr('src'));
            });
        }

        if ($crawler->filter('#tab-additional_information > table.shop_attributes')->count() > 0) {

            $crawler->filter('#tab-additional_information > table.shop_attributes  tr')->each(function (Crawler $node, $i) use (&$product) {
                $value = $node->filter('td')->text();

                switch ($node->filter('th')->text()) {
                    case 'Hauteur':

                        if (preg_match('/([0-9]+)cm/', $value, $matchs)) {
                            $product['height'] = $matchs['1'];
                        } elseif (preg_match('/([0-9]+)m/', $value, $matchs)) {
                            $product['height'] = $matchs['1'] * 100;
                        }
                        break;

                    case 'Largeur':

                        if (preg_match('/([0-9]+)cm/', $value, $matchs)) {
                            $product['width'] = $matchs['1'];
                        } elseif (preg_match('/([0-9]+)m/', $value, $matchs)) {
                            $product['width'] = $matchs['1'] * 100;
                        }
                        break;

                    case 'Profondeur':


                        if (preg_match('/([0-9]+)cm/', $value, $matchs)) {
                            $product['length'] = $matchs['1'];
                        } elseif (preg_match('/([0-9]+)m/', $value, $matchs)) {
                            $product['length'] = $matchs['1'] * 100;
                        }
                        break;
                }
            });
        }


        $product['crawlRef'] = $this->crawlRef;

        $doctrine = $this->getContainer()->get('doctrine');

        $p = $doctrine->getRepository('ProductBundle:Product')->findOneBy([
            'crawlRef' => $this->crawlRef,
            'crawlUqRef' => $product['sku'],
        ]);

        $this->totalProduct++;

        if ($p) {
            $output->writeln('[' . $this->crawlRef . '] - [PRODUIT] - [' . $product['sku'] . '] - IGNORE - DOUBLON => ' . $product['title']);
            $this->totalIgnore++;
        } else {
            $output->writeln('[' . $this->crawlRef . '] - [PRODUIT] - [' . $product['sku'] . '] - AJOUT - ' . $product['title']);

            $return = $this->saveProduct($product);

            if ($return == true) {
                $output->writeln('[' . $this->crawlRef . '] - [PRODUIT] - [' . $product['sku'] . '] - AJOUT - ' . $product['title'] . ' => OK');
                $this->totalInsert++;
            } else {
                $output->writeln('[' . $this->crawlRef . '] - [PRODUIT] - [' . $product['sku'] . '] - AJOUT - ' . $product['title'] . ' => ' . $return);
                $this->totalError++;
            }
        }

    }
}