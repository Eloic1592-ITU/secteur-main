<?php

namespace App\Form;

use App\Entity\MvtBonsValide;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class MvtBonsValideType extends AbstractType
{
    // public function buildForm(FormBuilderInterface $builder, array $options): void
    // {
    //     $numberField = function (string $label) {
    //         return [
    //             'label'    => $label,
    //             'scale'    => 2,
    //             'html5'    => true,
    //             'input'    => 'string',
    //             'required' => false,
    //             'attr'     => [
    //                 'step'  => '0.01',
    //                 'min'   => 0,
    //                 'class' => 'numeric-field'
    //             ],
    //             'constraints' => [
    //                 new Assert\Type([
    //                     'type' => 'numeric',
    //                     'message' => 'Veuillez entrer un nombre valide.'
    //                 ]),
    //             ],
    //         ];
    //     };

    //     $textField = function (string $label) {
    //         return [
    //             'label' => $label,
    //             'required' => false,
    //             'attr' => ['class' => 'text-field']
    //         ];
    //     };

    //     $builder
    //         // Champs visibles
    //         ->add('D_BONS', DateType::class, [
    //             'label' => 'Date Bons',
    //             'required' => false,
    //             'widget' => 'single_text', // Affiche un input HTML5 de type date
    //             'html5' => true,
    //             'attr' => ['class' => 'text-field']
    //         ])
    //         // ->add('NUMSEM', NumberType::class, [
    //         //     'label' => 'Numéro semaine',
    //         //     'html5' => true,
    //         //     'required' => false,
    //         //     'scale' => 0,
    //         //     'attr' => ['min' => 0]
    //         // ])
    //         // ->add('TXMP', NumberType::class, $numberField('Taux moyen pondéré (TXMP)'))

    //         ->add('NUMSEM',HiddenType::class)
    //         ->add('TXMP',HiddenType::class)

    //         // Champs cachés (hidden)
    //         ->add('NBRSS', HiddenType::class)
    //         ->add('NBRSS0', HiddenType::class)
    //         ->add('NBRSS1', HiddenType::class)
    //         ->add('NBRSS2', HiddenType::class)
    //         ->add('MAN', HiddenType::class)
    //         ->add('MAN1', HiddenType::class)
    //         ->add('MAN2', HiddenType::class)
    //         ->add('MSM', HiddenType::class)
    //         ->add('MSM1', HiddenType::class)
    //         ->add('MSM2', HiddenType::class)
    //         ->add('MAD', HiddenType::class)
    //         ->add('MAD1', HiddenType::class)
    //         ->add('MAD2', HiddenType::class)
    //         ->add('TXPMIN', HiddenType::class)
    //         ->add('TXPMAX', HiddenType::class)
    //         ->add('TXAMIN', HiddenType::class)
    //         ->add('TXAMAX', HiddenType::class);
    // }

    // public function configureOptions(OptionsResolver $resolver): void
    // {
    //     $resolver->setDefaults([
    //         'data_class' => MvtBonsValide::class,
    //     ]);
    // }
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
                'scale' => 2,
                'html5' => true,
                'mapped' => false,
                'attr' => ['step' => '0.01']
            ])
            ->add('taux_12_semaines', NumberType::class, [
                'label' => '12 semaines (2)',
                'required' => false,
                'scale' => 2,
                'html5' => true,
                'mapped' => false,
                'attr' => ['step' => '0.01']
            ])
            ->add('taux_24_semaines', NumberType::class, [
                'label' => '24 semaines (3)',
                'required' => false,
                'scale' => 2,
                'html5' => true,
                'mapped' => false,
                'attr' => ['step' => '0.01']
            ])
            ->add('taux_26_semaines', NumberType::class, [
                'label' => '26 semaines (4)',
                'required' => false,
                'scale' => 2,
                'html5' => true,
                'mapped' => false,
                'attr' => ['step' => '0.01']
            ])
            ->add('taux_52_semaines', NumberType::class, [
                'label' => '52 semaines (5)',
                'required' => false,
                'scale' => 2,
                'html5' => true,
                'mapped' => false,
                'attr' => ['step' => '0.01']
            ])
            
            // Section Offres Compétitives (pour plus tard)
            ->add('montant_annonce', NumberType::class, [
                'label' => 'Montant annoncé',
                'required' => false,
                'scale' => 2,
                'html5' => true,
                'mapped' => false,
                'attr' => ['step' => '0.01']
            ])
            ->add('montant_soumis', NumberType::class, [
                'label' => 'Montant soumis',
                'required' => false,
                'scale' => 2,
                'html5' => true,
                'mapped' => false,
                'attr' => ['step' => '0.01']
            ])
            ->add('montant_adjuge', NumberType::class, [
                'label' => 'Montant adjugé',
                'required' => false,
                'scale' => 2,
                'html5' => true,
                'mapped' => false,
                'attr' => ['step' => '0.01']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MvtBonsValide::class,
        ]);
    }
}
