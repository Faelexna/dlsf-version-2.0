<?php

namespace Cis\DlsfBundle\Form\Finance;

use Cis\DlsfBundle\Util\NominalCodeUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class IncomeDetailFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $costCentres = [
            NominalCodeUtil::COST_CENTRE_FE_U19, 
            NominalCodeUtil::COST_CENTRE_FE_O19,
            NominalCodeUtil::COST_CENTRE_ALL
        ];

        $builder
            ->add('costCentre', ChoiceType::class, [
                'choices' => array_combine($costCentres, $costCentres),
                'placeholder' => 'Please select',
                'label' => 'Cost Centre',
                'required' => true
            ])
            ->add('subDetail', CategoryType::class, [
                'choice_label' => 'subDetail',
                'academic_year' => $options['academic_year'],
                'finance_only' => true,
                'internal_only' => false,
                'label' => 'Sub Detail',
                'placeholder' => 'Please select',
                'required' => true
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Amount',
                'required' => true
            ])
        ;
    }
}
