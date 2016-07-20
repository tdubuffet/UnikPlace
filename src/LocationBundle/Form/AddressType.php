<?php

namespace LocationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', TextType::class, ['label' => 'Adresse', 'required' => true])
            ->add('city_code', TextType::class, ['mapped' => false, 'label' => 'Code postal'])
            ->add('city', TextType::class, ['mapped' => false, 'label' => 'Ville', 'disabled' => true])
            ->add('country', TextType::class, ['mapped' => false, 'label' => 'Pays', 'disabled' => true, 'data' => 'FRANCE'])
            ->add('save', SubmitType::class, ['label' => 'Ajouter cette adresse'])
            ;
    }
}