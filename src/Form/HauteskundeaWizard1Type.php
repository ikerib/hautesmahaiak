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
use Symfony\UX\Dropzone\Form\DropzoneType;

class HauteskundeaWizard1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('errolda', DropzoneType::class, [
                'attr' => [
                    'placeholder' => 'Aukeratu Errolda CSV fitxategia',
                ],
                'mapped' => false,
                'required' => true,
            ])
            ->add('zozketa', DropzoneType::class, [
                'attr' => [
                    'placeholder' => 'Aukeratu zozketa CSV fitxategia',
                ],
                'mapped' => false,
                'required' => true,
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
