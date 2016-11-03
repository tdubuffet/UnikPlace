<?php

namespace ProductBundle\Form;

use LocationBundle\Form\AddressAdminType;
use LocationBundle\Form\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
            ->add('quantity', null, ['label' => 'Quantité'])
            ->add('parcelHeight', null, ['label' => 'Hauteur du colis en cm'])
            ->add('parcelLength', null, ['label' => 'Longueur du colis en cm'])
            ->add('parcelWidth', null, ['label' => 'Largeur du colis en cm'])
            ->add('parcelType', ChoiceType::class, [
                'label' => 'Type de colis',
                'choices' => [
                    'Colis' => 'box',
                    'Encombrant' => 'bulky',
                    'Palette' => 'pallet',
                ]
            ])
            ->add('customDeliveryFee', NumberType::class, [
                'label' => 'Mes frais de port en France métropolitaine',
                'mapped' => false,
                'required' => false,
                'data' => $this->getCustomDeliveryFee($builder->getData())
            ])
            ->add('byHandDelivery', CheckboxType::class, [
                'label' => 'J\'accepte la remise en main propre',
                'mapped' => false,
                'required' => false,
                'data' => $this->isByHandDeliveryEnabled($builder->getData())
            ])
            ->add('emc', CheckboxType::class, [
                'label' => 'Le service livraison Unik Place',
                'mapped' => true,
                'required' => false
            ])
            ->add('address', AddressAdminType::class, ['label' => 'Adresse']);

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

    private function getCustomDeliveryFee($product)
    {
        $code = 'seller_custom';
        $deliveries = $product->getDeliveries();
        if (isset($deliveries)) {
            foreach ($deliveries as $delivery) {
                if ($delivery->getDeliveryMode()->getCode() == $code) {
                    return $delivery->getFee();
                }
            }
        }
        return null;
    }

    private function isByHandDeliveryEnabled($product)
    {
        $code = 'by_hand';
        $deliveries = $product->getDeliveries();
        if (isset($deliveries)) {
            foreach ($deliveries as $delivery) {
                if ($delivery->getDeliveryMode()->getCode() == $code) {
                    return true;
                }
            }
        }
        return false;
    }
}
