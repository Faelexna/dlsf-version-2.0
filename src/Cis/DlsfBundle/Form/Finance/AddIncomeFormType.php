<?php

namespace Cis\DlsfBundle\Form\Finance;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AddIncomeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('incomeDetail', CollectionType::class, [
                'entry_type' => IncomeDetailFormType::class,
                'error_bubbling' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => true
            ])
        ;
    }

}
