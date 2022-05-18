<?php

namespace Cis\DlsfBundle\Form\Type;

use App\Entity\Dlsf\Applicant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteType extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'choices' => [
				'No Site' => 'NONE',
				'North Devon' => 'BPL',
				'Mid Devon' => 'TIV'
			],
			'placeholder' => 'All',
			'required' => false,
			'label' => 'Campus'
		]);
	}
	
	public function getParent()
	{
		return ChoiceType::class;
	}
}