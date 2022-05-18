<?php

namespace Cis\DlsfBundle\Form\Applicant;

use App\Form\Dlsf\Type\FundingType;
use App\Form\Dlsf\Type\AgeCategoryType;
use App\Form\Type\ManagementLevelFilterType;
use Cis\DlsfBundle\Form\Type as Type;
use Petroc\CoreBundle\Form\Type\ToggleType;
use Petroc\CoreBundle\Form\Type\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SearchApplicantFormType extends AbstractType
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
					->add('faculty', ManagementLevelFilterType::class)
					->add('student', null, [
						'required' => false,
						'label' => 'Student'
					])
					->add('studentLevel', Type\StudentLevelType::class)
					->add('category', Type\CategoryType::class, [
						'academic_year' => $command->academicYear,
						'finance_only' => 0
					])
					->add('excludeCategories', Type\ExcludeCategoriesType::class, [
						'academic_year' => $command->academicYear
					])
					->add('evidenceSeen', YesNoType::class, [
						'label' => 'Evidence Seen',
						'required' => false
					])
					->add('lowIncomeEvidence', Type\LowIncomeEvidenceType::class)
					->add('approved', YesNoType::class, [
						'label' => 'Approved',
						'required' => false
					])
					->add('enhancedBursary', ToggleType::class, [
						'label' => 'Enhanced Bursary Only',
						'required' => false
					])
					->add('excludeHigherIncome', ToggleType::class, [
						'label' => 'Exclude Higher Income Learners',
						'required' => false
					])
					->add('householdIncomeOne', MoneyType::class, [
						'label' => 'Household Income',
						'required' => false,
						'help_text' => 'Ignores any with evidence seen and household income blank'
					])
					->add('householdIncomeTwo', MoneyType::class, [
						'label' => 'Between',
						'required' => false,
						'help_text' => 'Box One: Only equals, Box Two: Only less than, Both Boxes: Between'
					])
					->add('orderBy', Type\OrderByType::class)
				;
			})

		;
	}
}

