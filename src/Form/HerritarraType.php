<?php

namespace App\Form;

use App\Entity\Hauteskundea;
use App\Entity\Herritarra;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HerritarraType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dist')
            ->add('secc')
            ->add('mesa')
            ->add('nlocal')
            ->add('nlocalb')
            ->add('helbidea')
            ->add('barrutia')
            ->add('cargofinal')
            ->add('kargua')
            ->add('cargo')
            ->add('ident')
            ->add('nombre')
            ->add('apellido1')
            ->add('apellido2')
            ->add('eguna')
            ->add('hilabetea')
            ->add('urtea')
            ->add('jaioteguna', null, [
                'widget' => 'single_text',
            ])
            ->add('hauteskundea', EntityType::class, [
                'class' => Hauteskundea::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Herritarra::class,
        ]);
    }
}
