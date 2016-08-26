<?php

namespace ProductBundle\Form;

use LocationBundle\Form\AddressAdminType;
use LocationBundle\Form\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
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
            ->add('weight', null, ['label' => 'Poids'])
            ->add('width', null, ['label' => 'Largeur'])
            ->add('length', null, ['label' => 'Longueur'])
            ->add('height', null, ['label' => 'Hauteur'])
            ->add('category', null, ['label' => 'Catégorie'])
            ->add('currency', null, ['label' => 'Devise'])
            ->add('status', null, ['label' => 'Statut'])
            ->add('address', AddressAdminType::class, ['label' => 'Adresse'])
        ;
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
