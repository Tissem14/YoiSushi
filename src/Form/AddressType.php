<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Quel nom souhaitez-vous donner à votre adresse ?',
                'attr' => [
                    'placeholder' => 'Exemple : Bureau'
                ]
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Jean'
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Duchemin'
                ]
            ])
            ->add('company', TextType::class, [
                'label' => 'Votre société',
                'required' => false,
                'attr' => [
                    'placeholder' => '(Facultatif)'
                ]
            ])
            ->add('address', TextType::class, [
                'label' => 'Votre adresse',
                'attr' => [
                    'placeholder' => '7 rue Molière'
                ]
            ])
            ->add('city', ChoiceType::class, [
                'label' => 'Votre ville',
                'choices' => [
                    'Perpignan, 66000' => 'Perpignan, 66000',
                    'Claira, 66530' => 'Claira, 66530',
                    'Rivesaltes, 66600' => 'Rivesaltes, 66600',
                    'Saint-Laurent-de-la-Salanque, 66250' => 'Saint-Laurent-de-la-Salanque, 66250',
                    'Torreilles, 66440' => 'Torreilles, 66440',
                ],
                'placeholder' => 'Sélectionnez votre ville',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter mon adresse'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
