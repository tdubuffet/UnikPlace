<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 17/11/16
 * Time: 11:36
 */

namespace AppBundle\Service;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class TranslationLoader implements LoaderInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Insére les traductions en base de donnée dans le catalogue
     *
     * @param $resource
     * @param $locale
     * @param string $domain
     * @return MessageCatalogue
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        $catalogue = new MessageCatalogue($locale);

        $wordings = $this->em->getRepository('AppBundle:Wording')->findAll();

        foreach ($wordings as $trans) {
            //$catalogue->set($trans->getCode(), $trans->translate($locale, false)->getText());
        }

        return $catalogue;
    }

}