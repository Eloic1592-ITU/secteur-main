<?php

namespace App\Form;

use App\Entity\SecteurValide;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SecteurValideType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $anneeField = function (string $label) {
            return [
                'label' => $label,
                    'attr' => [
                        'min' => 1900,
                        'max' => 2100,
                        'step' => 1,
                        'class' => 'numeric-field'
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Range([
                            'min' => 1900,
                            'max' => 2100,
                            'notInRangeMessage' => 'L\'année doit être comprise entre {{ min }} et {{ max }}.'
                        ]),
                        new Assert\Type(['type' => 'integer'])
                    ]
            ];
        };

        $moisField = function (string $label) {
            return [
                'label' => $label,
                'choices' => [
                    'Janvier' => 1,
                    'Février' => 2,
                    'Mars' => 3,
                    'Avril' => 4,
                    'Mai' => 5,
                    'Juin' => 6,
                    'Juillet' => 7,
                    'Août' => 8,
                    'Septembre' => 9,
                    'Octobre' => 10,
                    'Novembre' => 11,
                    'Décembre' => 12,
                ],
                'placeholder' => 'Sélectionnez un mois',
                'attr' => [
                    'class' => 'form-select'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ];
        };


        $numberField = function (string $label) {
            return [
                'label' => $label,
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'step' => '0.01',
                    'min' => 0,
                    'class' => 'numeric-field'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'numeric'])
                ]
            ];
        };

        $builder
            ->add('annee', IntegerType::class, $anneeField('Année'))
            ->add('nummois', ChoiceType::class, $moisField('Numéro du mois'))
            ->add('ensemble', NumberType::class, $numberField('Ensemble'))
            ->add('prodvivr_n_t', NumberType::class, $numberField('Produits vivriers non transformés'))
            ->add('prodvivr_t_n_riz', NumberType::class, $numberField('Produits vivriers transformés (hors riz)'))
            ->add('prodvivr_t_riz', NumberType::class, $numberField('Produits vivriers transformés (riz)'))
            ->add('prodmanufind', NumberType::class, $numberField('Produits manufacturés industriels'))
            ->add('prodmanufart', NumberType::class, $numberField('Produits manufacturés artisanaux'))
            ->add('servpubl', NumberType::class, $numberField('Services publics'))
            ->add('servpriv', NumberType::class, $numberField('Services privés'))
            ->add('ppn', NumberType::class, $numberField('Produits de première nécessité (PPN)'));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SecteurValide::class,
        ]);
    }
}
