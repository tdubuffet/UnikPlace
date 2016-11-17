<?php

namespace Admin2Bundle\Controller;

use Admin2Bundle\Form\CreateCategoryForm;
use AppBundle\Entity\Translation;
use AppBundle\Entity\TranslationPage;
use AppBundle\Entity\Wording;
use AppBundle\Form\TranslationPageType;
use AppBundle\Form\TranslationType;
use AppBundle\Form\WordingType;
use ProductBundle\Entity\Attribute;
use ProductBundle\Entity\Category;
use ProductBundle\Entity\Collection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
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

    /**
     * @Route("/page/translation/list/{page}", name="ad2_translation_list")
     * @return Response
     */
    public function translationListAction(Request $request, TranslationPage $page)
    {

        $wordings = $this->getDoctrine()->getRepository('AppBundle:Wording')->findBy([
            'page' => $page
        ]);

        return $this->render('Admin2Bundle:Translation:translationlist.html.twig', [
            'wordings' => $wordings,
            'page' => $page
        ]);
    }

    /**
     * @Route("/page/translation/add/{page}", name="ad2_translation_add")
     * @return Response
     */
    public function translationAddAction(Request $request, TranslationPage $page)
    {

        $translation = new Wording();
        $translation->setPage($page);

        $form = $this->createForm(WordingType::class, $translation);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($translation);
            $this->getDoctrine()->getManager()->flush();

            $kernel = $this->get('kernel');
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput(array(
                'command' => 'cache:clear',
                '--env' => $this->get('kernel')->getEnvironment(),
            ));
            $output = new BufferedOutput();
            $application->run($input, $output);

            $content = $output->fetch();

            return $this->redirectToRoute('ad2_translation_list', ['page' => $page->getId()]);

        }


        return $this->render('Admin2Bundle:Translation:translationcreate.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/page/translation/edit/{page}/{wording}", name="ad2_translation_edit")
     * @return Response
     */
    public function translationEditAction(Request $request, TranslationPage $page, Wording $wording)
    {

        $wording->setPage($page);

        $form = $this->createForm(WordingType::class, $wording);
        $form->remove('code');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($wording);
            $this->getDoctrine()->getManager()->flush();


            $kernel = $this->get('kernel');
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput(array(
                'command' => 'cache:clear',
                '--env' => $this->get('kernel')->getEnvironment(),
            ));
            $output = new BufferedOutput();
            $application->run($input, $output);

            $content = $output->fetch();

            return $this->redirectToRoute('ad2_translation_list', ['page' => $page->getId()]);

        }


        return $this->render('Admin2Bundle:Translation:translationcreate.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/page/translation/delete/{page}/{wording}", name="ad2_translation_delete")
     * @return Response
     */
    public function translationRemoveAction(Request $request, TranslationPage $page, Wording $wording)
    {

        $this->getDoctrine()->getManager()->remove($wording);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('ad2_translation_list', ['page' => $page->getId()]);

    }
}
