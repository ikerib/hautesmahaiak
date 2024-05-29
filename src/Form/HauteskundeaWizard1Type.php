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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\UX\Dropzone\Form\DropzoneType;

class HauteskundeaWizard1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('izenaEu', TextType::class, [
                'label' => 'Hauteskundearen izarea',
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 3),
                ],
            ])
            ->add('izenaEs', TextType::class, [
                'label' => 'Denominación elecciones',
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 3),
                ],
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Aktibatu',
                'help' => 'Markatu hau hauteskundeko kontsulta eremua aktibatu / desaktibatzeko',
            ])
            ->add('data', DateType::class, options: [
                'constraints' => [
                    new NotBlank(),
                ],
                'label' => 'Data',
                'required' => true,
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hauteskundea::class,
        ]);
    }
}
