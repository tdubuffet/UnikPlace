<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 20/07/16
 * Time: 11:26
 */

namespace LocationBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CountyController
 * @package LocationBundle\Controller
 */
class CountyController extends Controller
{
    /**
     * @Route("/ajax/search/county", name="ajax_search_county")
     * @Method({"GET"})
     */
    public function countyListAction()
    {
        $list = $this->getDoctrine()->getRepository("LocationBundle:County")->findAllToArray();
        if (!$list) {
            return new JsonResponse(['message' => 'an error occured'], 500);
        }

        return new JsonResponse(['counties' => $list]);
    }

    /**
     * @Route("/ajax/search/city", name="ajax_search_city")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function searchCities(Request $request)
    {
        if ($request->request->has('q') && strlen($request->request->get('q')) >= 3) {
            $query = $request->request->get("q");

            $cities = $this->getDoctrine()->getRepository("LocationBundle:City")->createQueryBuilder('c')
                ->where('c.name LIKE :q')
                ->orWhere('c.zipcode LIKE :q')
                ->setParameter('q', '%'.$query.'%')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();

            $results = [];
            foreach ($cities as $city) {
                $results[] = ['id' =>$city->getId(),
                              'name' => $city->getName(),
                              'zipcode' => $city->getZipcode()];
            }
            return new JsonResponse(['cities' => $results]);
        }
        return new JsonResponse(['cities' => $results]);
    }


}