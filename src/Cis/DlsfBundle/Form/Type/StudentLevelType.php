<?php

namespace Cis\DlsfBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StudentLevelType extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'choices' => [
				'Level 2 and below' => 'BELOW_3',
				'Level 3 and above' => 'ABOVE_2'
			],
			'placeholder' => 'All',
			'required' => false,
			'label' => 'Student Level'
		]);
	}
	
	public function getParent()
	{
		return ChoiceType::class;
	}
}