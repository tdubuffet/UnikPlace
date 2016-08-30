<?php

namespace BlogBundle\Controller;

use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vich\UploaderBundle\Adapter\ORM\DoctrineORMAdapter;

/**
 * @Route("/blog")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="blog")
     * @Route("/{id}", name="blog_category")
     */
    public function indexAction(Request $request, $id = null)
    {
        $categories = $this->getDoctrine()->getRepository('BlogBundle:BlogCategory')->findAll();

        $query = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('a')
            ->from('\BlogBundle\Entity\Article', 'a')
            ->where('a.published = :published')
            ->setParameter('published', true);

        if ($id) {
            $query->andWhere('a.category = :category')
                ->setParameter('category', $id);
        }

        $pagerfanta = new Pagerfanta(new \Pagerfanta\Adapter\DoctrineORMAdapter($query));
        $pagerfanta->setMaxPerPage(50);

        try {
            $pagerfanta->setCurrentPage($request->get('page', 1));
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $this->render('BlogBundle:Default:index.html.twig', [
            'articles' => $pagerfanta,
            'categories' => $categories
        ]);
    }
}
