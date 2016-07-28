<?php

namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MangopayKYCLegalType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('headquarter_address_street', TextType::class, array(
                'label' => 'Rue de l\'entreprise'
            ))
            ->add('headquarter_address_postal_code', TextType::class, array(
                'label' => 'Code postal de l\'entreprise'
            ))
            ->add('headquarter_address_city', TextType::class, array(
                'label' => 'Ville de l\'enteprise'
            ))
            ->add('headquarter_address_country', CountryType::class, array(
                'label' => 'Pays de l\'entreprise',
                'preferred_choices' => array('FR')
            ))
            ->add('legal_representative_address_street', TextType::class, array(
                'label' => 'Rue du représentant légal'
            ))
            ->add('legal_representative_address_postal_code', TextType::class, array(
                'label' => 'Code postal du représentant légal'
            ))
            ->add('legal_representative_address_city', TextType::class, array(
                'label' => 'Ville du représentant légal'
            ))
            ->add('legal_representative_address_country', CountryType::class, array(
                'label' => 'Pays du représentant légal',
                'preferred_choices' => array('FR')
            ))
            ->add('certified_articles', FileType::class, array(
                'label' => 'Copie du mémo de l\'entreprise'
            ))
            ->add('proof_registration', FileType::class, array(
                'label' => 'Copie de la preuve d\'enregistrement de votre entreprise'
            ))
            ->add('shareholder_declaration', FileType::class, array(
                'label' => 'Copie de la déclaration d\'actionnaire'
            ))
            ->add('card_identity', FileType::class, array(
                'label' => 'Copie de la carte d\'identité du représentant légal'
            ))
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer mes informations',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ;
    }
}