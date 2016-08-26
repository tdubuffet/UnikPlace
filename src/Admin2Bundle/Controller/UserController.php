<?php

namespace Admin2Bundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="ad2_user_list")
     */
    public function listAction(Request $request)
    {


        $query = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('u')
            ->from('\UserBundle\Entity\User', 'u');

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($query));
        $pagerfanta->setMaxPerPage(10);

        try {
            $pagerfanta->setCurrentPage($request->get('page', 1));
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $this->render('Admin2Bundle:User:list.html.twig', [
            'users' => $pagerfanta
        ]);
    }
}
