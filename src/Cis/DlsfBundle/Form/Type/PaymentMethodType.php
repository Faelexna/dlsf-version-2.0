<?php

namespace Cis\DlsfBundle\Form\Type;

use App\Entity\Dlsf\Claim;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentMethodType extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver)
	{
		$paymentTypes = [
            Claim::PAYMENT_TYPE_BACS,
            Claim::PAYMENT_TYPE_BUDGET_TRANSFER,
			Claim::PAYMENT_TYPE_VOUCHER
        ];
        
        $resolver->setDefaults([
            'choices' => array_combine($paymentTypes, $paymentTypes),
            'placeholder' => 'Please Select',
            'required' => true,
			'label' => 'Payment Type'
        ]);
	}
	
	public function getParent()
	{
		return ChoiceType::class;
	}
}