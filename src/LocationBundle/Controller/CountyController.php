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
     * @Route("/ajax/search/city", name="ajax_search_city", options={"expose"=true})
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function searchCities(Request $request)
    {

        $results = [];

        if ($request->request->has('q') && strlen($request->request->get('q')) >= 3) {
            $query = $request->request->get("q");

            $cities = $this->getDoctrine()->getRepository("LocationBundle:City")->createQueryBuilder('c')
                ->where('c.name LIKE :q')
                ->orWhere('c.zipcode LIKE :q')
                ->setParameter('q', '%'.$query.'%')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();

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