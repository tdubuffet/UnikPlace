<?php

namespace LocationBundle\Form;

use Symfony\Component\Form\AbstractType;
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
            ->add('name', TextType::class, ['label' => 'Nom du destinataire', 'required' => true])
            ->add('street', TextType::class, ['label' => 'Adresse', 'required' => true])
            ->add('additional', TextType::class, ['label' => 'Complément d\'adresse', 'required' => false])
            ->add('country', TextType::class, ['mapped' => false, 'label' => 'Pays', 'disabled' => true, 'data' => 'FRANCE'])
            ->add('save', SubmitType::class, ['label' => 'Ajouter cette adresse'])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'allow_extra_fields' => true
        ));
    }
}