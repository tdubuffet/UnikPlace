<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 26/08/16
 * Time: 14:36
 */

namespace Admin2Bundle\Controller;


use Admin2Bundle\Form\CurrencyForm;
use ProductBundle\Entity\Currency;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StatusController
 * @package Admin2Bundle\Controller
 * @Route("/currency")
 */
class CurrencyController extends Controller
{
    /**
     * @Route("/", name="ad2_currency_list")
     * @return Response
     */
    public function listCurrencyAction()
    {
        $twigArray['currencies'] = $this->getDoctrine()->getRepository("ProductBundle:Currency")->findAll();
        foreach ($twigArray['currencies'] as $key => $st) {
            $twigArray['count'][$key] = $this->getDoctrine()
                ->getRepository("ProductBundle:Product")->countByCurrency($st);
        }

        return $this->render('Admin2Bundle:Currency:list.html.twig', $twigArray);
    }

    /**
     * @Route("/new", name="ad2_currency_new")
     * @param Request $request
     * @return Response|RedirectResponse
     */
    public function newCurrencyAction(Request $request)
    {
        $form = $this->createForm(CurrencyForm::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Currency $currency */
            $currency = $form->getData();
            $this->getDoctrine()->getManager()->persist($currency);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash("success", sprintf('La devise %s a bien été ajoutée.', $currency->getCode()));

            return $this->redirectToRoute('ad2_currency_list');
        }

        return $this->render('Admin2Bundle:Currency:new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/edit/{id}", name="ad2_currency_edit")
     * @ParamConverter("currency", class="ProductBundle:Currency")
     * @param Request $request
     * @param Currency $currency
     * @return Response|RedirectResponse
     */
    public function editStatusAction(Request $request, Currency $currency)
    {
        $form = $this->createForm(CurrencyForm::class, $currency);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($currency);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash("success", sprintf('La devise %s a bien été modifiée.', $currency->getCode()));

            return $this->redirectToRoute('ad2_currency_list');
        }

        return $this->render('Admin2Bundle:Currency:new.html.twig',
            ['form' => $form->createView(), 'currency' => $currency]);
    }
}