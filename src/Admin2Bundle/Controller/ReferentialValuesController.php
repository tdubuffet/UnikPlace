<?php

namespace Admin2Bundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use ProductBundle\Entity\Referential;
use ProductBundle\Entity\ReferentialValue;
use ProductBundle\Form\ReferentialValueType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
/**
 * @Route("/referential-values")
 */
class ReferentialValuesController extends Controller
{
    /**
     * @Route("/{id}", name="ad2_ref_values_list")
     */
    public function listAction(Request $request, Referential $referential)
    {
        return $this->render('Admin2Bundle:ReferentialValues:index.html.twig', [
            'referential' => $referential
        ]);
    }

    /**
     * @Route("/edit/{ref}/{id}", name="ad2_ref_values_edit")
     *
     * @ParamConverter("referential", class="ProductBundle:Referential", options={"id" = "ref"})
     * @ParamConverter("referentialValue", class="ProductBundle:ReferentialValue", options={"id" = "id"})
     */
    public function editAction(Request $request, Referential $referential, ReferentialValue $referentialValue)
    {

        $form = $this->createForm(ReferentialValueType::class, $referentialValue);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->getDoctrine()->getManager()->persist($referentialValue);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ad2_ref_values_list', ['id' => $referential->getId()]);

        }

        return $this->render('Admin2Bundle:ReferentialValues:edit.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/add/{ref}", name="ad2_ref_values_add")
     *
     * @ParamConverter("referential", class="ProductBundle:Referential", options={"id" = "ref"})
     */
    public function addAction(Request $request, Referential $referential)
    {

        $referentialValue = new ReferentialValue();
        $referentialValue->addReferential($referential);

        $form = $this->createForm(ReferentialValueType::class, $referentialValue);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->getDoctrine()->getManager()->persist($referentialValue);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ad2_ref_values_list', ['id' => $referential->getId()]);

        }

        return $this->render('Admin2Bundle:ReferentialValues:edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
