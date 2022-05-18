<?php

namespace Cis\DlsfBundle\Form\Applicant;

use App\Form\Dlsf\Type\AgeCategoryType;
use App\Entity\Dlsf\Applicant;
use Cis\DlsfBundle\Form\Type as Form;
use Petroc\CoreBundle\Form\Type\ToggleType;
use Petroc\CoreBundle\Form\Type\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class EditApplicantFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('ageCategory', AgeCategoryType::class, [
				'label' => 'Age Category',
				'placeholder' => false
			])
			->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $formEvent) {
                $form = $formEvent->getForm();
                $command = $formEvent->getData();

                $applicant = $command->getApplicant();
                if ($applicant->getAgeCategory() === Applicant::AGE_CATEGORY_OVER_19_NAME) {
					$form
						->add('liveWithPartner', YesNoType::class, [
							'label' => 'I live with a partner'
						])
						->add('financiallyIndependent', YesNoType::class, [
							'label' => 'I(We) are financially independent'
						])
						->add('numberOfDependents', NumberType::class, [
							'label' => 'Number of dependent children who are under 16, or under 19 and in full time education'
						])
						->add('twentyFourPlusEvidenceSeen', ToggleType::class, [
							'label' => 'Advanced Learning Loans Evidence Seen'
						])
						->add('parentBenefits', Form\BenefitsType::class, [
							'label' => 'Benefits received by this person',
							'academic_year' => $applicant->getAcademicYear(),
							'group_label' => 'O19'
						])
					;
				} else {
					$form
						->add('liveIndependently', YesNoType::class, [
							'label' => 'Do you live independently?'
						])
						->add('incomeSupport', YesNoType::class, [
							'label' => 'Are you in receipt of income support?'
						])
						->add('inCare', YesNoType::class, [
							'label' => 'Are you in care?'
						])
						->add('careLeaver', YesNoType::class, [
							'label' => 'Are you a care leaver?'
						])
						->add('esaOrDsa', YesNoType::class ,[
							'label' => 'Are you a person with a disability and in receipt of Employment Support Allowance (ESA) who are also in receipt of Disability Living Allowance (DLA)?'
						])
						->add('liveWithParent', YesNoType::class, [
							'label' => 'Do you live with your Parent/Guardian?'
						])
						->add('parentBenefits', Form\BenfitsType::class, [
							'label' => 'Benefits received by Parent/Guardian',
							'academic_year' => $applicant->getAcademicYear(),
							'group_label' => 'U19'
						])
					;
			}})
			->add('showOnEnhancedReport', ToggleType::class, [
				'label' => 'Show on Enhanced Bursary Report'
			])
			->add('notOnMainstreamProgramme', ToggleType::class, [
				'label' => 'Not on mainstream programme'
			])
			->add('householdIncome', MoneyType::class, [
				'label' => 'Household Income'
			])
			->add('lowIncomeEvidence', Form\LowIncomeEvidenceType::class, [
				'label' => 'Low Income Evidence Type',
				'placeholder' => 'N/A'
			])
			->add('lowIncomeEvidenceOther', TextType::class, [
				'label' => 'Low Income Evidence Other'
			])
			->add('evidenceType', Form\EvidenceSeenType::class, [
				'label' => 'Evidence Seen'
			])
			->add('bankAccountHolder', TextType::class, [
				'label' => 'Bank Account Holder'
			])
			->add('bankName', TextType::class, [
				'label' => 'Bank Name'
			])
			->add('bankSortCode', TextType::class, [
				'label' => 'Sort Code'
			])
			->add('bankAccountNumber', TextType::class, [
				'label' => 'Account Number'
			])
		;
	}
}