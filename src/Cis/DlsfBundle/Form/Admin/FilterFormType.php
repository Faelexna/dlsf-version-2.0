<?php

namespace Cis\DlsfBundle\Form\Admin;

use App\Form\Dlsf\Type\FundingType;
use App\Form\Dlsf\Type\AgeCategoryType;
use Cis\DlsfBundle\Form\Type as Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $formEvent) {
                $form = $formEvent->getForm();
                $command = $formEvent->getData();

                $form
                    ->add('ageCategory', AgeCategoryType::class, [
                        'label' => 'Applicant Type',
                        'placeholder' => 'All'
                    ])
                    ->add('fundingType', FundingType::class, [
                        'label' => 'Funding Type'
                    ])
                    ->add('site', Type\SiteType::class)
                    ->add('category', Type\CategoryType::class, [
						'academic_year' => $command->getAcademicYear(),
						'finance_only' => 0
					])
                ;
            })
        ;
    }
}
