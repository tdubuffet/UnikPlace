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

class MangopayKYCNaturalType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('occupation', ChoiceType::class, array(
                'label' => 'Métier',
                'choice_label' => function ($value) {
                    return $value;
                },
                'choices'  => [
                    "Aéronautique et espace",
                    "Agriculture - Agroalimentaire",
                    "Agroalimentaire - industries alimentaires",
                    "Artisanat",
                    "Audiovisuel, cinéma",
                    "Audit, comptabilité, gestion",
                    "Automobile",
                    "Banque, assurance",
                    "Bâtiment, travaux publics",
                    "Biologie, chimie, pharmacie",
                    "Commerce, distribution",
                    "Communication",
                    "Création, métiers d'art",
                    "Culture, patrimoine",
                    "Défense, sécurité",
                    "Documentation, bibliothèque",
                    "Droit",
                    "Edition, livre",
                    "Enseignement",
                    "Environnement",
                    "Ferroviaire",
                    "Foires, salons et congrès",
                    "Fonction publique",
                    "Hôtellerie, restauration",
                    "Humanitaire",
                    "Immobilier",
                    "Industrie",
                    "Informatique, télécoms, Web",
                    "Marketing, publicité",
                    "Médical",
                    "Mode-textile",
                    "Paramédical",
                    "Propreté et services associés",
                    "Psychologie",
                    "Ressources humaines",
                    "Sciences humaines et sociales",
                    "Secrétariat",
                    "Social",
                    "Spectacle - Métiers de la scène",
                    "Sport",
                    "Tourisme",
                    "Transport-Logistique"
                ],
                'required' => true,
                'multiple' => false
            ))
            ->add('income_range', ChoiceType::class, array(
                'label' => 'Salaire',
                'choices'  => [
                    "Moins de 18 000€" => 1,
                    "Entre 18 000€ et 30 000 €" => 2,
                    "Entre 30 000€ et 50 000€" => 3,
                    "Entre 50 000€ et 80 000€" => 4,
                    "Entre 80 000€ et 120 000€" => 5,
                    "Plus de 120 000€" => 6
                ],
                'required' => true,
                'multiple' => false
            ))
            ->add('address_street', TextType::class, array(
                'label' => 'Rue du domicile'
            ))
            ->add('address_postal_code', TextType::class, array(
                'label' => 'Code postal du domicile'
            ))
            ->add('address_city', TextType::class, array(
                'label' => 'Ville'
            ))
            ->add('address_country', CountryType::class, array(
                'label' => 'Pays',
                'preferred_choices' => array('FR')
            ))
            ->add('identity_file', FileType::class, array(
                'label' => 'Copie de la carte d\'identité'
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