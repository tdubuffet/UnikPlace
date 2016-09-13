<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SiteMapController extends Controller
{
    /**
     * @Route("/sitemap.{_format}", name="sitemap", Requirements={"_format" = "xml"})
     * @Template("AppBundle:default:sitemap.html.twig")
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request)
    {

        $router = $this->get('router');

        $urls = [];
        /** Homepage */
        $urls[] = [
            'priority'  => '1',
            'url'       => $router->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        /** Contact */
        $urls[] = [
            'priority'  => '0.5',
            'url'       => $router->generate('contact', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ];


        /** about */
        $urls[] = [
            'priority'  => '1',
            'url'       => $router->generate('about', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        /** faq */
        $urls[] = [
            'priority'  => '1',
            'url'       => $router->generate('faq', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        /** quality_content */
        $urls[] = [
            'priority'  => '0.5',
            'url'       => $router->generate('quality_content', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        /** Categories */
        $categories = $this->getDoctrine()
            ->getRepository('ProductBundle:Category')
            ->findByParentCache(null);

        foreach($categories as $category) {

            $urls[] = [
                'priority'  => '0.9',
                'url'       => $router->generate('category', [
                    'path' => $category->getPath()
                ], UrlGeneratorInterface::ABSOLUTE_URL)
            ];

            if (count($category->getChildren()) > 0) {
                foreach($category->getChildren() as $subCategory) {
                    $urls[] = [
                        'priority'  => '0.9',
                        'url'       => $router->generate('category', [
                            'path' => $subCategory->getPath()
                        ], UrlGeneratorInterface::ABSOLUTE_URL)
                    ];
                }
            }

        }

        /** Collections */
        $urls[] = [
            'priority'  => '0.8',
            'url'       => $router->generate('collections', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        /** Collections */
        $collections = $this->getDoctrine()->getRepository("ProductBundle:Collection")->findLast10();

        foreach($collections as $collection) {

            $urls[] = [
                'priority'  => '0.8',
                'url'       => $router->generate('collection', [
                    'slug' => $collection->getSlug()
                ], UrlGeneratorInterface::ABSOLUTE_URL)
            ];

        }

        /** Products */
        $products = $this->getDoctrine()->getRepository("ProductBundle:Product")->findByStatus([2, 5]);

        foreach($products as $product) {

            $urls[] = [
                'priority'  => '0.7',
                'url'       => $router->generate('product_details', [
                    'slug' => $product->getSlug(),
                    'id' => $product->getId()
                ], UrlGeneratorInterface::ABSOLUTE_URL)
            ];

        }

        return [
            'urls' => $urls
        ];
    }
}
