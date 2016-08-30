<?php

namespace Admin2Bundle\Controller;

use Admin2Bundle\Model\AttributesProduct;
use BlogBundle\Form\ArticleType;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use ProductBundle\Entity\AttributeValue;
use ProductBundle\Entity\Image;
use ProductBundle\Entity\Product;
use ProductBundle\Form\ImageType;
use ProductBundle\Form\ProductType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/blog/article")
 */
class BlogArticleController extends Controller
{
    /**
     * @Route("/", name="ad2_blog_article_list")
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request)
    {
        $search = $request->get('search');

        $query = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('a')
            ->from('\BlogBundle\Entity\Article', 'a');

        if ($search) {
            $query->where('a.title LIKE :search or a.description LIKE :search OR a.id LIKE :search')
                ->setParameter('search', "%$search%");
        }

        $pagerfanta = new Pagerfanta(new DoctrineORMAdapter($query));
        $pagerfanta->setMaxPerPage(50);

        try {
            $pagerfanta->setCurrentPage($request->get('page', 1));
        } catch(NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $this->render('Admin2Bundle:Article:list.html.twig', ['articles' => $pagerfanta]);
    }

    /**
     * @Route("/add", name="ad2_blog_article_add")
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request)
    {

        $form = $this->createForm(ArticleType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->getDoctrine()->getManager()->persist($form->getData());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ad2_blog_article_list');

        }

        return $this->render('Admin2Bundle:Article:add.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
