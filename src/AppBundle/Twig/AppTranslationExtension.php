<?php
/**
 * Created by PhpStorm.
 * User: tdubuffet
 * Date: 17/11/16
 * Time: 12:32
 */

namespace AppBundle\Twig;

use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\TranslatorInterface;

class AppTranslationExtension extends TranslationExtension
{
    public function __construct(
        TranslatorInterface $translator,
        \Twig_NodeVisitorInterface $translationNodeVisitor = null)
    {
        parent::__construct($translator, $translationNodeVisitor);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('trans', array(
                $this,
                'trans'
            )),
        );
    }

    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getTranslator()->getLocale();
        }

        if (null === $domain) {
            $domain = 'messages';
        }

        if ('messages' !== $domain
            && false === $this->translationExists($id, $domain, $locale)
        ) {
            $domain = 'messages';
        }

        return $this->getTranslator()->trans($id, $parameters, $domain, $locale);
    }

    protected function translationExists($id, $domain, $locale)
    {
        return $this->getTranslator()->getCatalogue($locale)->has((string)$id, $domain);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app_translator';
    }
}