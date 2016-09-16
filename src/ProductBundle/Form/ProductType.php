<?php

namespace ProductBundle\Form;

use LocationBundle\Form\AddressAdminType;
use LocationBundle\Form\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, ['label' => 'Nom'])
            ->add('description', null, ['label' => 'Description'])
            ->add('price', null, ['label' => 'Prix'])
            ->add('originalPrice', null, ['label' => 'Prix d\'origine'])
            ->add('allowOffer', null, ['label' => 'Autoriser l\'offre'])
            ->add('weight', null, ['label' => 'Poids en kg'])
            ->add('width', null, ['label' => 'Largeur en cm'])
            ->add('length', null, ['label' => 'Longueur en cm'])
            ->add('height', null, ['label' => 'Hauteur en cm'])
            ->add('category', null, ['label' => 'Catégorie'])
            ->add('currency', null, ['label' => 'Devise'])
            ->add('status', null, ['label' => 'Statut'])
            ->add('address', AddressAdminType::class, ['label' => 'Adresse'])
        ;

        // Transform weight in grams / kilograms
        $builder->get('weight')
            ->addModelTransformer(new CallbackTransformer(
                function ($weightToKiloGrams) {
                    return $weightToKiloGrams / 1000;
                },
                function ($weightToGrams) {
                    return $weightToGrams * 1000;
                }
            ));

        // Transform width, length and height in meters / centimers
        $metersConversionCallbackTransformer = new CallbackTransformer(
            function ($valueToMeters) {
                return $valueToMeters * 100;
            },
            function ($valueToCentimeters) {
                return $valueToCentimeters / 100;
            }
        );
        $builder->get('width')->addModelTransformer($metersConversionCallbackTransformer);
        $builder->get('length')->addModelTransformer($metersConversionCallbackTransformer);
        $builder->get('height')->addModelTransformer($metersConversionCallbackTransformer);

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ProductBundle\Entity\Product'
        ));
    }
}
