<?php

namespace Cis\DlsfBundle\Form\Type;

use App\Entity\Misc\GeneralCode;
use App\Form\Type\GeneralCodeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BenefitsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
		$resolver->setRequired(['academic_year', 'group_label']);
        $resolver->setDefaults([
            'category' => GeneralCode::CATEGORY_STUDENT_DLSF_BENEFIT_TYPE,
			'expanded' => true,
			'multiple' => true
        ]);
    }
    public function getParent()
    {
        return GeneralCodeType::class;
    }
}
