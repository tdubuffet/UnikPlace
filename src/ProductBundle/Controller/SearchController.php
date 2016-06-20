<?php

namespace ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{
    /**
     * @Route("/recherche", name="search")
     * @Template("ProductBundle:Search:index.html.twig")
     */
    public function searchAction(Request $request)
    {

        $query = $request->get('q');
        $results = [];

        if ($query) {


            $finder = $this->container->get("fos_elastica.finder.noname.product");

            $boolQuery = new \Elastica\Query\Bool();


            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('name', $query);
            $boolQuery->addShould($fieldQuery);

            $fieldQuery = new \Elastica\Query\Fuzzy();
            $fieldQuery->setField('name', $query);
            $boolQuery->addShould($fieldQuery);

            $fieldQuery = new \Elastica\Query\Match();
            $fieldQuery->setFieldQuery('description', $query);
            $boolQuery->addShould($fieldQuery);

            $results = $finder->find($boolQuery);


        }

        return ['products' => $results];

    }
}
