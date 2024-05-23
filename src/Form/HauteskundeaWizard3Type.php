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

class HauteskundeaWizard3Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mezuaHautatuaEu', HiddenType::class, [
                'label' => 'Mezua hautatua bada:',
                'help' => 'Herritarrari erakutsiko zaion mezua hautatua izan bada.',
                'help_attr' => [
                    'class' => 'mt-0important text-sm text-gray-500 dark:text-gray-400',
                ],
                'attr' => [
                    'class' => 'stimulus-trix',
                    'data-controller' => 'trix',
                ],
            ])
            ->add('mezuaHautatuaEs', HiddenType::class, [
                'label' => 'Mensage si seleccionado:',
                'help' => 'Mensaje que se mostrará si el ciudadano ha sido seleccionado.',
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
