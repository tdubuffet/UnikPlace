<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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

    /**
     * @Route("/a/{slug}", name="blog_article")
     * @ParamConverter("article", class="BlogBundle:Article", options={"slug" = "slug"})
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
