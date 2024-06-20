<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $actualDate = new \DateTime('now');
        $builder
            ->add('title', null, [
                'label' => 'Event title',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a title for the event',
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a description for the event',
                    ])
                ]
            ])
            ->add('date', DateTimeType::class, [
//                'label' => 'Date',
//                'date_widget' => 'single_text',
//                'time_widget' => 'single_text',
//                'input' => 'datetime_immutable',
                'widget' => 'single_text',


                'attr' => [

                    'class' => 'form-control input-inline datepicker',

                    'data-provide' => 'datepicker',

                    'data-date-format' => 'dd-mm-yyyy'

                ],
                'constraints' => [
                    new GreaterThan([
                        'value' => $actualDate,
                        'message' => 'The date must be in the future.',
                    ]),
                    new NotBlank([
                        'message' => 'Please enter a date for the event',
                    ]),
                ],
            ])
            ->add('numberParticipants', NumberType::class, [
                'label' => 'Maximum number of participants',
                'rounding_mode' => 0,
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 2,
                        'message' => 'The number of participants must be at least 2.',
                    ]),
                    new NotBlank([
                        'message' => 'Please enter the maximum number of participants',
                    ]),
                ],
            ]
            )
            ->add('public', CheckboxType::class, [
                'label' => 'Is the event public ?',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
