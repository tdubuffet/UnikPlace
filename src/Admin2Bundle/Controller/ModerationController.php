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
 * @Route("/ad2/moderation")
 */
class ModerationController extends Controller
{
    /**
     * @Route("/", name="ad2_moderation_list")
     */
    public function listAction(Request $request)
    {
        $query = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('u')
            ->from('\ProductBundle\Entity\Product', 'u')
            ->where('u.status = :status')
            ->setParameter('status', 1);

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($query));
        $pagerfanta->setMaxPerPage(10);

        try {
            $pagerfanta->setCurrentPage($request->get('page', 1));
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $this->render('Admin2Bundle:Moderation:list.html.twig', [
            'products' => $pagerfanta
        ]);
    }
}
