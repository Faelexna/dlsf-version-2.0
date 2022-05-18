<?php

namespace Cis\DlsfBundle\Form\Applicant;

use Cis\DlsfBundle\Form\Type as Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class EditClaimFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $formEvent) {
                $form = $formEvent->getForm();
                $command = $formEvent->getData();
				
				$applicant = $command->getApplicant();
				$form
					->add('category', Type\CategoryType::class, [
						'required' => true,
						'academic_year' => $applicant->getAcademicYear(),
						'finance_only' => 0
					])
					->add('amount', MoneyType::class, [
						'required' => false,
						'label' => 'Amount'
					])
					->add('approvedAmount', MoneyType::class, [
						'required' => false,
						'label' => 'Amount Approved'
					])
					->add('paymentType', Type\PaymentMethodType::class)
					->add('notes', TextareaType::class, [
						'label' => 'Notes',
						'required' => false
					])
				;
			})
		;
	}
}
		