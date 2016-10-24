<?php

namespace LocationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('civility', ChoiceType::class, [
                'label' => 'Civilité',
                'choices' => [
                    'Monsieur' => 'mr',
                    'Madame' => 'mrs'
                ],
                'required' => true
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Nom du destinataire',
                'required' => true
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Prénom du destinataire',
                'required' => true
            ])
            ->add('street', TextType::class, [
                'label' => 'Votre adresse',
                'required' => true,
                'attr' => [
                ]
            ])
            ->add('additional', TextType::class, [
                'label' => 'Complément d\'adresse',
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ajouter cette adresse'
            ])
            ->add('street_number', HiddenType::class)
            ->add('sublocality_level_1', HiddenType::class)
            ->add('locality', HiddenType::class)
            ->add('route', HiddenType::class)
            ->add('administrative_area_level_1', HiddenType::class)
            ->add('postal_code', HiddenType::class)
            ->add('country', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'allow_extra_fields' => true
        ));
    }
}