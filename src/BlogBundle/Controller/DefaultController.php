<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vich\UploaderBundle\Adapter\ORM\DoctrineORMAdapter;

/**
 * @Route("/journal")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="blog")
     * @Route("/{id}", name="blog_category")
     * @param Request $request
     * @param null $id
     * @return Response
     */
    public function indexAction(Request $request, $id = null)
    {
        $categories = $this->getDoctrine()->getRepository('BlogBundle:BlogCategory')->findAll();
        $query = $this->getDoctrine()->getRepository("BlogBundle:Article")->getQueryByCategory($id);
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

    /**
     * @Route("/a/{slug}", name="blog_article")
     * @ParamConverter("article", class="BlogBundle:Article", options={"slug" = "slug"})
     * @param Request $request
     * @param Article $article
     * @return Response
     */
    public function articleAction(Request $request, Article $article)
    {
        $categories = $this->getDoctrine()->getRepository('BlogBundle:BlogCategory')->findAll();

        return $this->render('BlogBundle:Default:article.html.twig', [
            'article' => $article,
            'categories' => $categories
        ]);
    }
}
