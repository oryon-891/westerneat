<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'attr' => [
                    'placeholder' => "Entrez votre nom ..."
                ]
            ])
            ->add('lastname', TextType::class, [
                'attr' => [
                    'placeholder' => "Entrez votre prénom ..."
                ]
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => "Entrez votre email ..."
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Choississez le type de compte' => "ROLE_UNKNOWN",
                    'Utilisateur' => 'ROLE_USER',
                    'Commercant' => 'ROLE_VENDOR',
                    'Administrateur' => 'ROLE_ADMIN'
                ]
            ])
            ->add('password', PasswordType::class, [
                'attr' => [
                    'placeholder' => "Entrez votre mot de passe ..."
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => "S'inscrire",
                'attr' => [
                    'class' => 'btn-dark'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
