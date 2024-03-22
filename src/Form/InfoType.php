<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('firstname', TextType::class, [
            'attr' => [
                'placeholder' => "Entrez votre nom ...",
            ],
            'required' => false
        ])
        ->add('lastname', TextType::class, [
            'attr' => [
                'placeholder' => "Entrez votre prénom ..."
            ],
            'required' => false
        ])
        ->add('email', EmailType::class, [
            'attr' => [
                'placeholder' => "Entrez votre email ..."
            ],
            'required' => false
        ])
        ->add('old_password', PasswordType::class, [
            'attr' => [
                'placeholder' => "Entrez l'ancien mot de passe ..."
            ],
            'required' => false
        ])
        ->add('new_password', RepeatedType::class, [
            'type' => PasswordType::class,
            'mapped' => false,
            'invalid_message' => 'Le mot de passe et la confirmation doivent être identique.',
            'label' => false,
            'required' => true,
            'first_options' => [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Merci de saisir votre nouveau mot de passe.'
                ]
            ],
            'second_options' => [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Merci de confirmer votre nouveau mot de passe.'
                ]
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            /* 'data_class' => User::class, */
        ]);
    }
}
