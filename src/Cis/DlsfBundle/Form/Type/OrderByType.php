<?php

namespace Cis\DlsfBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderByType extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'choices' => [
				'Surname, First Name' => 'name',
				'Date Evidence Seen' =>  'evidence_seen',
				'Date Applied' => 'timestamp'
			],
			'required' => false,
			'data' => 'name'
		]);
	}
	
	public function getParent()
	{
		return ChoiceType::class;
	}
}