<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\Email;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastname', null, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your last name',
                    ]),
                ],
            ])
            ->add('firstname', null, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your first name',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your email',
                    ]),
                    new Email([
                        'message' => 'Please enter a valid email address',
                    ]),
                    new Regex([
                        'pattern' => '/^[^@]+@[^@]+\.[a-zA-Z]{2,}$/',
                        'match' => true,
                        'message' => 'Invalid email format.',
                    ]),
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            'max' => 4096,
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[a-zA-Z])(?=.*\d).+$/',
                            'match' => true,
                            'message' => 'Your password must contain both letters and numbers',
                        ]),
                    ],
                    'label' => 'Password (At least 8 characters, including numbers and letters)',
                ],
                'second_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please confirm your password',
                        ]),
                    ],
                    'mapped' => false,
                    'attr' => ['autocomplete' => 'new-password'],
                    'label' => 'Confirm Password',
                ],
                'invalid_message' => 'The password fields must match.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'constraints' => [
                new UniqueEntity([
                    'fields' => 'email',
                    'message' => 'This email is already in use.',
                ]),
            ],
        ]);
    }
}
