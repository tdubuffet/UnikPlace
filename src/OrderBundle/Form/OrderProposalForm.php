<?php
/**
 * Created by PhpStorm.
 * User: francois
 * Date: 29/07/16
 * Time: 16:09
 */

namespace OrderBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrderProposalForm
 * @package OrderBundle\Form
 */
class OrderProposalForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('amount', IntegerType::class, ['label' => 'Prêt à acheter ? Faites une offre',
            'attr' => ['max' => $options['max'], 'min' => 1, 'placeholder' => 'Prix souhaité']])
            ->add('submit', SubmitType::class, ['label' => 'Soumettre l\'offre']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data-class' => 'OrderBundle\Entity\OrderProposal','max' => null]);
    }


}