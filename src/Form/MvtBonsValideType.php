<?php

namespace App\Form;

use App\Entity\MvtBonsValide;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use App\Form\DataTransformer\CommaToPointTransformer;
class MvtBonsValideType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ Date
            ->add('D_BONS', DateType::class, [
                'label' => 'Date des Bons',
                'required' => true,
                'widget' => 'single_text',
                'html5' => true,
                'mapped' => false
            ])
            
            // Section Taux Moyen Pondéré
            ->add('taux_4_semaines', NumberType::class, [
                'label' => '4 semaines (1)',
                'required' => false,
                'mapped' => false,
                'grouping' => true,
                'attr' => ['placeholder' => '0.00']
            ])
            ->add('taux_12_semaines', NumberType::class, [
                'label' => '12 semaines (2)',
                'required' => false,
                'mapped' => false,
                'grouping' => true,
                'attr' => ['placeholder' => '0.00']
            ])
            ->add('taux_24_semaines', NumberType::class, [
                'label' => '24 semaines (3)',
                'required' => false,
                'mapped' => false,
                'grouping' => true,
                'attr' => ['placeholder' => '0.00']
            ])
            ->add('taux_26_semaines', NumberType::class, [
                'label' => '26 semaines (4)',
                'required' => false,
                'mapped' => false,
                'grouping' => true,
                'attr' => ['placeholder' => '0.00']
            ])
            ->add('taux_52_semaines', NumberType::class, [
                'label' => '52 semaines (5)',
                'required' => false,
                'mapped' => false,
                'grouping' => true,
                'attr' => ['placeholder' => '0.00']
            ])
            
            // Section Offres Compétitives
            ->add('montant_annonce', NumberType::class, [
                'label' => 'Montant annoncé',
                'required' => false,
                'mapped' => false,
                'grouping' => true,
                'attr' => ['placeholder' => '0.00']
            ])
            ->add('montant_soumis', NumberType::class, [
                'label' => 'Montant soumis',
                'required' => false,
                'mapped' => false,
                'grouping' => true,
                'attr' => ['placeholder' => '0.00']
            ])
            ->add('montant_adjuge', NumberType::class, [
                'label' => 'Montant adjugé',
                'required' => false,
                'mapped' => false,
                'grouping' => true,
                'attr' => ['placeholder' => '0.00']
            ]);

        // Appliquer le transformer à tous les champs numériques
        $transformer = new CommaToPointTransformer();
        
        $builder->get('taux_4_semaines')->addModelTransformer($transformer);
        $builder->get('taux_12_semaines')->addModelTransformer($transformer);
        $builder->get('taux_24_semaines')->addModelTransformer($transformer);
        $builder->get('taux_26_semaines')->addModelTransformer($transformer);
        $builder->get('taux_52_semaines')->addModelTransformer($transformer);
        $builder->get('montant_annonce')->addModelTransformer($transformer);
        $builder->get('montant_soumis')->addModelTransformer($transformer);
        $builder->get('montant_adjuge')->addModelTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MvtBonsValide::class,
        ]);
    }
}
