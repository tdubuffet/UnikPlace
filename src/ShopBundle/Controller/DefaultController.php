<?php

namespace ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UserBundle\Entity\User;

class DefaultController extends Controller
{
    /**
     * @Route("/boutique/{id}", name="shop")
     * @ParamConverter("user", class="UserBundle:User")
     * @Template("ShopBundle:Default:index.html.twig")
     */
    public function indexAction(Request $request, User $user)
    {
        if (!$user->getPro()) {
            throw new NotFoundHttpException('Shop is only available for pro users');
        }

        $params         = $request->query->all();
        $params['user'] = $user->getId();
        $search         = $this->get('product_bundle.product_search_service');
        $results        = $search->search($params);
        $pagination     = $search->getHtmlPagination($results, $params);

        $repository = $this->getDoctrine()
            ->getRepository('ProductBundle:Category');
        $mainCategories = $repository
            ->findBy(array('parent' => null));

        return [
            'products' => $results,
            'mainCategories' => $mainCategories,
            'pagination' => $pagination,
            'user' => $user
        ];
    }
}
