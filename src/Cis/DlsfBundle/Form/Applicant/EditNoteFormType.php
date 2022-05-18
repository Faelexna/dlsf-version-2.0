<?php

namespace Cis\DlsfBundle\Form\Applicant;

use App\Entity\Dlsf\Note;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class EditNoteFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
				->add('notes', TextareaType::class, [
					'label' => 'Note',
					'required' => true
				])
			;
	}
}
		