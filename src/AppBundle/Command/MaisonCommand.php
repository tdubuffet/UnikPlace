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

class MaisonCommand extends ContainerAwareCommand
{

    private $urls = [
        'Mobilier' => 'http://www.maison-objet.com/fr/paris/exposants?q=&sector=0&category=0%2Ffurniture&country=0&sort=10&page=1',
        'Eclairage' => 'http://www.maison-objet.com/fr/paris/exposants?q=&sector=0&category=0%2Flighting&country=0&sort=10&page=1',
        'Objets déco' => 'http://www.maison-objet.com/fr/paris/exposants?q=&sector=0&category=0%2Fdecorative_item&country=0&sort=10&page=1',
        'Art de la table' => 'http://www.maison-objet.com/fr/paris/exposants?q=&sector=0&category=0%2Ftableware&country=0&sort=10&page=1',
        'Cuisine' => 'http://www.maison-objet.com/fr/paris/exposants?q=&sector=0&category=0%2Fkitchen&country=0&sort=10&page=1',
        'Outdoor/Mobilier Jardin' => 'http://www.maison-objet.com/fr/paris/exposants?q=&sector=0&category=1%2Foutdoor%2Fgarden_furniture&country=0&sort=10&page=1',
        'Outdoor/Décor' => 'http://www.maison-objet.com/fr/paris/exposants?q=&sector=0&category=1%2Foutdoor%2Fdecoration&country=0&sort=10&page=1',
        'Collectivité' => 'http://www.maison-objet.com/fr/paris/exposants?q=&sector=0&category=0%2Fhospitalities_contracts&country=0&sort=10&page=1',
        'Kids' => 'http://www.maison-objet.com/fr/paris/exposants?q=&sector=0&category=0%2Fkids&country=0&sort=10&page=1',
        'Tapis' => 'http://www.maison-objet.com/fr/paris/exposants?q=&sector=0&category=0%2Frugs&country=0&sort=10&page=1'
    ];

    private $handle;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName("crawl:maison")
            ->setDescription("Crawler de merde - Cariporel");
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

        $this->handle = fopen('list-expo.csv', 'w+');
        fputcsv($this->handle, [
            'Catégorie',
            'Titre',
            'Page profil',
            'Emplacement',
            'Pays',
            'Site web',
            'Nom/Prénom',
            'Adresse',
            'tel',
            'fax',
            'email'
        ], ';');

        foreach ($this->urls as $key => $url) {
            $this->findPage($url, $key);
        }
    }

    private function findPage($url, $key)
    {

        $crawler = new Crawler(file_get_contents($url));

        $crawler->filter('.set--exhibitors .invisi-hit')->each(function (Crawler $node, $i) use ($key) {

            $data = [];

            $data['category'] = $key;
            $data['title'] = $node->filter('h3')->first()->text();
            $data['fp'] = $node->filter('.invisi-hit__link')->first()->attr('href');

            $node->filter('.action')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {

                    $node->parentNode->removeChild($node);
                }
            });

            $data['hall-salon'] = '';
            $val = $node->filter('.subtle')->each(function (Crawler $crawler) use ($data) {


                foreach ($crawler as $node) {

                    $node->parentNode->removeChild($node);
                }

                return $crawler->text();
            });

            if (isset($val[0])) {
                $data['hall-salon'] = $val[0];
            }


            $data['country'] = trim($node->filter('.b_box-title-desc')->first()->text());

            $this->findFp($data);


        });

        if ($crawler->filter('.pagination__control--right .hit[rel="next"]')->count() > 0) {
            $url = $crawler->filter('.pagination__control--right .hit[rel="next"]')->first()->attr('href');
            $this->findPage('http://www.maison-objet.com' . $url, $key);
        }
    }

    private function findFp($data)
    {

        var_dump($data);

        $crawler = new Crawler(file_get_contents($data['fp']));


        if ($crawler->filter('.exhibitor-infos a')->count()) {
            $data['url'] = $crawler->filter('.exhibitor-infos a')->first()->attr('href');
        }

        if ($crawler->filter('.exhibitor-contact .col')->count() > 0) {

            $dom = $crawler->filter('.exhibitor-contact .col')->first();

            if ($dom->filter('h3')->count() > 0) {
                $data['user'] = trim($dom->filter('h3')->text());
            } else {
                $data['user'] = '';
            }

            if ($dom->filter('p')->count() > 0) {
                $data['address'] = trim($dom->filter('p')->first()->text());
            } else {
                $data['address'] = '';
            }

            if ($dom->filter('#tel')->count() > 0) {
                $data['tel'] = trim($dom->filter('#tel + span')->first()->text());
            } else {
                $data['tel'] = '';
            }

            if ($dom->filter('#fax')->count() > 0) {
                $data['fax'] = trim($dom->filter('#fax + span')->first()->text());
            } else {
                $data['fax'] = '';
            }

            $data['email'] = '';

            if ($dom->filter('a')->count() > 0) {

                $dom->filter('a')->each(function (Crawler $crawler) use ($data) {

                    $value = $crawler->attr('href');

                    $value = str_replace('mailto:', '', $value);

                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $data['email'] = $value;


                        $this->saveData($data);
                    }
                });

            }


        }
    }

    public function saveData($data)
    {

        fputcsv($this->handle, array_values($data), ';');

    }

}