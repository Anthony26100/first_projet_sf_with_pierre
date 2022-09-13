<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegisterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Username :',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Votre Username'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Password : ',
                    'attr' => [
                    'placeholder' => 'Entrez votre mot de passe'
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Entrez un mot de passe'
                        ]),
                        new Regex([
                            'pattern' => '/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9]).{6,})\S$/',
                            'message' => 'Votre mot de passe doit comporter au moins 6 caractères, une lettre majuscule, une lettre miniscule et 1 chiffre sans espace blanc'
                        ])
                    ]
                ],
                'second_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Répétez votre mot de passe'
                    ],
                    'invalid_message' => 'Le mot de passe doivent matcher',
                ],
                'mapped' => false,
                    
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prenom :',
                'attr' => [
                    'placeholder' => 'Veuillez rentrer votre prenom'
                ]
            ])
            ->add('age', NumberType::class,[
                'label' => 'Age : ',
                'attr' => [
                    'placeholder' => 'Age'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email :',
                'attr' => [
                    'placeholder' => 'Veuillez entrer votre email'
                ]
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville :',
                'attr' => [
                    'placeholder' => 'Veuillez entrer ville'
                ]
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom :',
                'attr' => [
                    'placeholder' => 'Veuillez rentrer votre nom'
                ]
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'download_uri' => false,
                'image_uri' => true,
                'label' => 'Image:',
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
