<?php

namespace Admin2Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use OrderBundle\Entity\FeeRate;

/**
 * FeeRate controller.
 *
 * @Route("/fee-rate")
 */
class FeeRateController extends Controller
{
    /**
     * Lists all FeeRate entities.
     *
     * @Route("/", name="ad2_fee_rate_list")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $feeRates = $em->getRepository('OrderBundle:FeeRate')->findBy([], ['type' => 'ASC', 'minimum' => 'ASC']);

        $feeRatesByType = [];
        $badConfiguration = false;
        foreach ($feeRates as $feeRate) {
            if (!isset($feeRatesByType[$feeRate->getType()])) {
                $feeRatesByType[$feeRate->getType()] = [];
                // Check for minimum
                if ($feeRate->getMinimum() != 0) {
                    $badConfiguration = true;
                }
            }
            $feeRatesByType[$feeRate->getType()][] = $feeRate;
        }

        return $this->render('Admin2Bundle:FeeRate:index.html.twig', array(
            'feeRates' => $feeRates,
            'feeRatesByType' => $feeRatesByType,
            'badConfiguration' => $badConfiguration,
        ));
    }

    /**
     * Creates a new FeeRate entity.
     *
     * @Route("/new", name="ad2_fee_rate_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $feeRate = new FeeRate();
        $form = $this->createForm('OrderBundle\Form\FeeRateType', $feeRate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($feeRate);
            $em->flush();

            return $this->redirectToRoute('ad2_fee_rate_show', array('id' => $feeRate->getId()));
        }

        return $this->render('Admin2Bundle:FeeRate:new.html.twig', array(
            'feeRate' => $feeRate,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a FeeRate entity.
     *
     * @Route("/{id}", name="ad2_fee_rate_show")
     * @Method("GET")
     */
    public function showAction(FeeRate $feeRate)
    {
        $deleteForm = $this->createDeleteForm($feeRate);

        return $this->render('Admin2Bundle:FeeRate:show.html.twig', array(
            'feeRate' => $feeRate,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing FeeRate entity.
     *
     * @Route("/{id}/edit", name="ad2_fee_rate_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, FeeRate $feeRate)
    {
        $deleteForm = $this->createDeleteForm($feeRate);
        $editForm = $this->createForm('OrderBundle\Form\FeeRateType', $feeRate);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($feeRate);
            $em->flush();

            return $this->redirectToRoute('ad2_fee_rate_edit', array('id' => $feeRate->getId()));
        }

        return $this->render('Admin2Bundle:FeeRate:edit.html.twig', array(
            'feeRate' => $feeRate,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a FeeRate entity.
     *
     * @Route("/{id}", name="ad2_fee_rate_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, FeeRate $feeRate)
    {
        $form = $this->createDeleteForm($feeRate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($feeRate);
            $em->flush();
        }

        return $this->redirectToRoute('ad2_fee_rate_list');
    }

    /**
     * Creates a form to delete a FeeRate entity.
     *
     * @param FeeRate $feeRate The FeeRate entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(FeeRate $feeRate)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ad2_fee_rate_delete', array('id' => $feeRate->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
