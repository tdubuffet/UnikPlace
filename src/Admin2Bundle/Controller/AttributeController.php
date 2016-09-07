<?php

namespace Admin2Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ProductBundle\Entity\Attribute;

/**
 * Attribute controller.
 *
 * @Route("/attribute")
 */
class AttributeController extends Controller
{
    /**
     * Lists all Attribute entities.
     *
     * @Route("/", name="ad2_attribute_list")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $attributes = $em->getRepository('ProductBundle:Attribute')->findAll();

        return $this->render('Admin2Bundle:Attribute:index.html.twig', array(
            'attributes' => $attributes,
        ));
    }

    /**
     * Creates a new Attribute entity.
     *
     * @Route("/new", name="ad2_attribute_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $attribute = new Attribute();
        $form = $this->createForm('ProductBundle\Form\AttributeType', $attribute);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($attribute);
            $em->flush();

            return $this->redirectToRoute('ad2_attribute_show', array('id' => $attribute->getId()));
        }

        return $this->render('Admin2Bundle:Attribute:new.html.twig', array(
            'attribute' => $attribute,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Attribute entity.
     *
     * @Route("/{id}", name="ad2_attribute_show")
     * @Method("GET")
     */
    public function showAction(Attribute $attribute)
    {
        $deleteForm = $this->createDeleteForm($attribute);

        return $this->render('Admin2Bundle:Attribute:show.html.twig', array(
            'attribute' => $attribute,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Attribute entity.
     *
     * @Route("/{id}/edit", name="ad2_attribute_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Attribute $attribute)
    {
        $deleteForm = $this->createDeleteForm($attribute);
        $editForm = $this->createForm('ProductBundle\Form\AttributeType', $attribute);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($attribute);
            $em->flush();

            return $this->redirectToRoute('ad2_attribute_edit', array('id' => $attribute->getId()));
        }

        return $this->render('Admin2Bundle:Attribute:edit.html.twig', array(
            'attribute' => $attribute,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Attribute entity.
     *
     * @Route("/{id}", name="ad2_attribute_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Attribute $attribute)
    {
        $form = $this->createDeleteForm($attribute);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($attribute);
            $em->flush();
        }

        return $this->redirectToRoute('ad2_attribute_list');
    }

    /**
     * Creates a form to delete a Attribute entity.
     *
     * @param Attribute $attribute The Attribute entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Attribute $attribute)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ad2_attribute_delete', array('id' => $attribute->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
