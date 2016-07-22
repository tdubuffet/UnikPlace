<?php

namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class PreferenceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('emailClassified', CheckboxType::class, array(
                'label'    => 'Recevoir des emails de vos annonces',
                'required' => false
            ))
            ->add('newsletter', CheckboxType::class, array(
                'label'    => 'Recevoir des emails d\'informations de NoName',
                'required' => false
            ))
            ->add('submit', SubmitType::class, array(
                'label' => "Enregistrer",
                'attr' => [
                    'class' => 'btn-primary'
                ]
            ));
    }

    public function getName()
    {
        return 'app_form_profil_preference';
    }
}