<?php

namespace Admin2Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ProductBundle\Entity\Referential;
use ProductBundle\Form\ReferentialType;

/**
 * Referential controller.
 *
 * @Route("/referential")
 */
class ReferentialController extends Controller
{
    /**
     * Lists all Referential entities.
     *
     * @Route("/", name="ad2_ref_list")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $referentials = $em->getRepository('ProductBundle:Referential')->findAll();

        return $this->render('Admin2Bundle:Referential:index.html.twig', array(
            'referentials' => $referentials,
        ));
    }

    /**
     * Creates a new Referential entity.
     *
     * @Route("/new", name="ad2_ref_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $referential = new Referential();
        $form = $this->createForm('ProductBundle\Form\ReferentialType', $referential);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($referential);
            $em->flush();

            return $this->redirectToRoute('ad2_ref_show', array('id' => $referential->getId()));
        }

        return $this->render('Admin2Bundle:Referential:new.html.twig', array(
            'referential' => $referential,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Referential entity.
     *
     * @Route("/{id}", name="ad2_ref_show")
     * @Method("GET")
     */
    public function showAction(Referential $referential)
    {
        $deleteForm = $this->createDeleteForm($referential);

        return $this->render('Admin2Bundle:Referential:show.html.twig', array(
            'referential' => $referential,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Referential entity.
     *
     * @Route("/{id}/edit", name="ad2_ref_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Referential $referential)
    {
        $deleteForm = $this->createDeleteForm($referential);
        $editForm = $this->createForm('ProductBundle\Form\ReferentialType', $referential);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($referential);
            $em->flush();

            return $this->redirectToRoute('ad2_ref_edit', array('id' => $referential->getId()));
        }

        return $this->render('Admin2Bundle:Referential:edit.html.twig', array(
            'referential' => $referential,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Referential entity.
     *
     * @Route("/{id}", name="ad2_ref_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Referential $referential)
    {
        $form = $this->createDeleteForm($referential);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($referential);
            $em->flush();
        }

        return $this->redirectToRoute('ad2_ref_index');
    }

    /**
     * Creates a form to delete a Referential entity.
     *
     * @param Referential $referential The Referential entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Referential $referential)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ad2_ref_delete', array('id' => $referential->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
