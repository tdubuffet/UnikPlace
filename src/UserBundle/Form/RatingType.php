<?php

namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RatingType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rate', ChoiceType::class, array(
                'choices'  => [
                    'Excellent' => 5,
                    'Très bien' => 4,
                    'Bien' => 3,
                    'Décevant' => 2,
                    'A éviter' => 1
                ],
                'required' => true,
                'expanded' => true,
                'multiple' => false
            ))
            ->add('message', TextareaType::class, [
                'required' => true
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider ma note',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UserBundle\Entity\Rating'
        ));
    }
}
