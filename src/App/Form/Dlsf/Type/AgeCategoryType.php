<?php

namespace App\Form\Dlsf\Type;

use App\Entity\Misc\GeneralCode;
use App\Form\Type\GeneralCodeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgeCategoryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'category' => GeneralCode::CATEGORY_STUDENT_DLSF_AGE_CATEGORY,
        ]);
    }
    public function getParent()
    {
        return GeneralCodeType::class;
    }
}
