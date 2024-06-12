<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePhoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Ajoute le champ pour afficher l'ancien numéro de téléphone de manière figée
        $builder
            ->add('phone', TelType::class, [
                'label' => 'Votre ancien numéro de téléphone', // Label du champ
                'disabled' => true, // Rend le champ désactivé
            ])
            ->add('new_phone', TelType::class, [
                'mapped' => false, // Ce champ ne sera pas mappé à l'entité User
                'required' => true, // Champ obligatoire
                'label' => 'Nouveau numéro de téléphone', // Label du champ
            ])
            ->add('submit', SubmitType::class, [
                'label' => "Changer le numéro de téléphone", // Label du bouton de soumission
                'attr' => [
                    'class' => 'btn btn-primary' // Classe CSS pour styliser le bouton
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Configuration des options par défaut du formulaire
        $resolver->setDefaults([
            'data_class' => User::class, // Classe de l'entité utilisée pour le formulaire
        ]);
    }
}
