<?php

namespace App\Form;

use App\Entity\Hauteskundea;
use App\Entity\Herritarra;
use App\Repository\HerritarraRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrdezkatuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('HerritarraAll', EntityType::class, [
                'label' => 'Herritar honekin',
                'class' => Herritarra::class,
                'query_builder' => function (HerritarraRepository $er) use ($options) {
                    return $er->createQueryBuilder('h')
                        ->andWhere('h.secc = :secc')->setParameter('secc', $options['secc'])
                        ->andWhere('h.dist = :dist')->setParameter('dist', $options['dist'])
                        ->andWhere('h.mesa = :mesa')->setParameter('mesa', $options['mesa'])
                        ->andWhere('h.id != :id')->setParameter('id', $options['current_id'])
                        ->orderBy('h.cargofinal','ASC')
                        ;
                },
                'mapped' => false
            ])
            ->add('NextHerritarra', EntityType::class, [
                'label' => 'Herritar honekin',
                'class' => Herritarra::class,
                'query_builder' => function (HerritarraRepository $er) use ($options) {
                    return $er->createQueryBuilder('h')
                        ->andWhere('h.id = :id')->setParameter('id', $options['next_id'])
                        ;
                },
                'mapped' => false
            ])
            ->add('active', ChoiceType::class, [
                'choices' => [
                    'Alta' => 1,
                    'Ordezkoa' => 0,
                    'Baja' => -1
                ]
            ])
            ->add('hauteskundea', EntityType::class, [
                'class' => Hauteskundea::class,
                'choice_label' => 'id',
            ])
            ->add('current_id', HiddenType::class, [
                'data' => $options['current_id'],
                'mapped' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Herritarra::class,
            'secc' => null,
            'dist' => null,
            'mesa' => null,
            'current_id' => null,
            'next_id' => null
        ]);
    }
}
