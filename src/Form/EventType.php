<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $actualDate = new \DateTime('now');
        $builder
            ->add('title')
            ->add('description')
            ->add('date', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'constraints' => [
                    new GreaterThan([
                        'value' => $actualDate,
                        'message' => 'The date must be in the future.',
                    ]),
                ],
            ])
            ->add('numberParticipants')
            ->add('public')
//            ->add('creator', EntityType::class, [
//                'class' => User::class,
//                'choice_label' => 'id',
//            ])
//            ->add('participants', EntityType::class, [
//                'class' => User::class,
//                'choice_label' => 'id',
//                'multiple' => true,
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
