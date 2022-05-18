<?php

namespace App\Repository\Dlsf;

class ApplicantCriteria
{
	public $academicYear;
	public $ageCategory;
	public $fundingType;
	public $site;
	public $faculty;
	public $student;
	public $studentLevel;
	public $category;
	public $excludeCategories;
	public $evidenceSeen;
	public $lowIncomeEvidence;
	public $approved;
	public $enhancedBursary = false;
	public $excludeHigherIncome = false;
	public $householdIncomeOne;
	public $householdIncomeTwo;
	public $orderBy;
}