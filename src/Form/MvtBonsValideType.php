<?php

namespace App\Form;

use App\Entity\MvtBonsValide;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;
// use Symfony\Component\Form\Extension\Core\Type\TextType;

class MvtBonsValideType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $numberField = function (string $label) {
            return [
                'label'    => $label,
                'scale'    => 2,
                'html5'    => true,
                'input'    => 'string',
                'required' => false,
                'attr'     => [
                    'step'  => '0.01',
                    'min'   => 0,
                    'class' => 'numeric-field'
                ],
                'constraints' => [
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'Veuillez entrer un nombre valide.'
                    ]),
                ],
            ];
        };

        $textField = function (string $label) {
            return [
                'label' => $label,
                'required' => false,
                'attr' => ['class' => 'text-field']
            ];
        };

        $builder
            ->add('D_BONS', TextType::class, $textField('D Bons'))
            ->add('NUMSEM', NumberType::class, [
                'label' => 'Numéro semaine',
                'html5' => true,
                'required' => false,
                'scale' => 0, // entier
                'attr' => ['min' => 0]
            ])
            ->add('NBRSS', NumberType::class, $numberField('Nombre de semaines (NBRSS)'))
            ->add('NBRSS0', NumberType::class, $numberField('Nombre de semaines 0 (NBRSS0)'))
            ->add('NBRSS1', NumberType::class, $numberField('Nombre de semaines 1 (NBRSS1)'))
            ->add('NBRSS2', NumberType::class, $numberField('Nombre de semaines 2 (NBRSS2)'))
            ->add('MAN', NumberType::class, $numberField('Montant annoncé (MAN)'))
            ->add('MAN1', NumberType::class, $numberField('Montant annoncé 1 (MAN1)'))
            ->add('MAN2', NumberType::class, $numberField('Montant annoncé 2 (MAN2)'))
            ->add('MSM', NumberType::class, $numberField('Montant soumis (MSM)'))
            ->add('MSM1', NumberType::class, $numberField('Montant soumis 1(MSM1)'))
            ->add('MSM2', NumberType::class, $numberField('Montant soumis 2(MSM2)'))
            ->add('MAD', NumberType::class, $numberField('Montant adjugé (MAD)'))
            ->add('MAD1', NumberType::class, $numberField('Montant adjugé 1 (MAD1)'))
            ->add('MAD2', NumberType::class, $numberField('Montant adjugé 2 (MAD2)'))
            ->add('TXPMIN', NumberType::class, $numberField('Taux proposés minimum (TXPMIN)'))
            ->add('TXPMAX', NumberType::class, $numberField('Taux proposés maximum (TXPMAX)'))
            ->add('TXAMIN', NumberType::class, $numberField(' (TXAMIN)'))
            ->add('TXAMAX', NumberType::class, $numberField(' (TXAMAX)'))
            ->add('TXMP', NumberType::class, $numberField('Taux moyen pondéré (TXMP)'));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MvtBonsValide::class,
        ]);
    }
}
