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
     * @Route("/ajax/recherche/county", name="ajax_search_county")
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
     * @Route("/ajax/recherche/city", name="ajax_search_city")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getCityByZipCodeAction(Request $request)
    {
        if ($request->request->has('zipcode')) {
            $zipcode = $request->request->get("zipcode");
            $city = $this->getDoctrine()->getRepository("LocationBundle:City")->findByZipcodeToArray($zipcode);
            if (!$city) {
                return new JsonResponse(['message' => "City not found"], 404);
            }

            return new JsonResponse(['city' => $city]);
        }

        return new JsonResponse(['message' => "City not found"], 404);
    }


}