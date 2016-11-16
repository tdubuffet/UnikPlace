<?php

namespace Admin2Bundle\Controller;

use Admin2Bundle\Form\CreateCategoryForm;
use AppBundle\Entity\TranslationPage;
use AppBundle\Form\TranslationPageType;
use ProductBundle\Entity\Attribute;
use ProductBundle\Entity\Category;
use ProductBundle\Entity\Collection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/traduction")
 */
class TranslationController extends Controller
{
    /**
     * @Route("/langue", name="ad2_language")
     * @return Response
     */
    public function listAction()
    {
        $data = $this->getDoctrine()->getRepository('AppBundle:Language')->findAll();

        return $this->render('Admin2Bundle:Translation:list.html.twig', [
            'languages' => $data
        ]);
    }

    /**
     * @Route("/page", name="ad2_pages")
     * @return Response
     */
    public function pagesAction()
    {
        $data = $this->getDoctrine()->getRepository('AppBundle:TranslationPage')->findAll();

        return $this->render('Admin2Bundle:Translation:pageslist.html.twig', [
            'pages' => $data
        ]);
    }

    /**
     * @Route("/page/add", name="ad2_pages_add")
     * @return Response
     */
    public function pagesAddAction(Request $request)
    {

        $page = new TranslationPage();

        $form = $this->createForm(TranslationPageType::class, $page);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->getDoctrine()->getManager()->persist($page);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ad2_pages');

        }

        return $this->render('Admin2Bundle:Translation:pagescreate.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
