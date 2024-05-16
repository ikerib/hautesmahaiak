<?php

namespace App\Form;

use App\Entity\Hauteskundea;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HauteskundeaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('izena', TextType::class, [
                'label' => 'Hauteskundea',
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Aktibatu',
            ])
            ->add('data', DateType::class, [
                'label' => 'Data',
                'widget' => 'single_text',
                'html5' => false,
                'help' => 'Noiz izango dira hauteskundeak.',
                'help_attr' => [
                    'class' => 'mt-0important text-sm text-gray-500 dark:text-gray-400',
                ],
                'attr' => [
                    'data-controller' => 'datepicker',
                    'class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 pl-10 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
                ],
                'label_attr' => [
                ],
            ])
            ->add('oharrak', HiddenType::class, [
                'label' => 'Deskribapena:',
                'help' => 'Hemen eskaintzari buruzko informazio areagotua zehaztu daiteke',
                'help_attr' => [
                    'class' => 'mt-0important text-sm text-gray-500 dark:text-gray-400',
                ],
                'attr' => [
                    'class' => 'stimulus-trix',
                    'data-controller' => 'trix',
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hauteskundea::class,
        ]);
    }
}
