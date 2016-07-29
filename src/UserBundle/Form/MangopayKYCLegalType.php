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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

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
                'label' => 'Rue de l\'entreprise',
                'required' => true,
                'constraints' => array(
                    new NotBlank())
            ))
            ->add('headquarter_address_postal_code', TextType::class, array(
                'label' => 'Code postal de l\'entreprise',
                'required' => true,
                'constraints' => array(
                    new NotBlank())
            ))
            ->add('headquarter_address_city', TextType::class, array(
                'label' => 'Ville de l\'enteprise',
                'required' => true,
                'constraints' => array(
                    new NotBlank())
            ))
            ->add('headquarter_address_country', CountryType::class, array(
                'label' => 'Pays de l\'entreprise',
                'preferred_choices' => array('FR'),
                'required' => true
            ))
            ->add('legal_representative_address_street', TextType::class, array(
                'label' => 'Rue du représentant légal',
                'required' => true,
                'constraints' => array(
                    new NotBlank())
            ))
            ->add('legal_representative_address_postal_code', TextType::class, array(
                'label' => 'Code postal du représentant légal',
                'required' => true,
                'constraints' => array(
                    new NotBlank())
            ))
            ->add('legal_representative_address_city', TextType::class, array(
                'label' => 'Ville du représentant légal',
                'required' => true,
                'constraints' => array(
                    new NotBlank())
            ))
            ->add('legal_representative_address_country', CountryType::class, array(
                'label' => 'Pays du représentant légal',
                'preferred_choices' => array('FR'),
                'required' => true
            ))
            ->add('certified_articles', FileType::class, array(
                'label' => 'Copie du mémo de l\'entreprise',
                'constraints' => [
                    new File([
                        'maxSize' => '6M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                    ])
                ]
            ))
            ->add('proof_registration', FileType::class, array(
                'label' => 'Copie de la preuve d\'enregistrement de votre entreprise',
                'constraints' => [
                    new File([
                        'maxSize' => '6M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                    ])
                ]
            ))
            ->add('shareholder_declaration', FileType::class, array(
                'label' => 'Copie de la déclaration d\'actionnaire',
                'constraints' => [
                    new File([
                        'maxSize' => '6M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                    ])
                ]
            ))
            ->add('card_identity', FileType::class, array(
                'label' => 'Copie de la carte d\'identité du représentant légal',
                'constraints' => [
                    new File([
                        'maxSize' => '6M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                    ])
                ]
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