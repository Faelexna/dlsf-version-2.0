<?php

namespace Cis\DlsfBundle\CommandBus\Applicant;

use App\Entity\Dlsf\Applicant;
use App\Entity\User;
use Petroc\Component\CommandBus\Command;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class EditApplicantCommand extends Command
{
	const GROUP_LOW_INCOME_OTHER = 'other';
	const GROUP_DEFAULT = 'Default';
	
	public $ageCategory;
	public $financiallyIndependent;
	public $liveWithPartner;
	public $liveWithParent;
	public $liveIndependently;
	public $numberOfDependents;
	public $twentyFourPlusEvidenceSeen;
	public $parentBenefits;
	public $showOnEnhancedReport;
	public $notOnMainstreamProgramme;
	public $householdIncome;
	public $lowIncomeEvidence;
	public $lowIncomeEvidenceOther;
	public $incomeSupport;
	public $evidenceType;
	public $inCare;
	public $careLeaver;
	public $esaOrDsa;
	public $evidenceSeen;
	public $evidenceSeenUser;
	public $evidenceSeenDate;
	public $bankAccountHolder;
	public $bankName;
	public $bankSortCode;
	public $bankAccountNumber;
        
	private $applicant;
	private $user;
	
    public function __construct(Applicant $applicant, User $user)
    {
        $this->applicant = $applicant;
		$this->user = $user;
        $this->mapData($applicant);
    }
    
    public function getApplicant(): Applicant
    {
        return $this->applicant;
    }
	
	public function getUser(): User
	{
		return $this->user;
	}
	
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
		$metadata->setGroupSequenceProvider(true);

		$allGroups = [self::GROUP_DEFAULT, self::GROUP_LOW_INCOME_OTHER];

        $metadata->addPropertyConstraint('lowIncomeEvidenceOther', new Assert\NotBlank([
			'groups' => self::GROUP_LOW_INCOME_OTHER,
		]));

		$metadata->addPropertyConstraint('lowIncomeEvidence', new Assert\Blank([	
			'groups' => self::GROUP_DEFAULT,
		]));

		$metadata->addPropertyConstraint('bankSortCode', new Assert\Length([
			'max' => Applicant::MAX_LENGTH_SORT_CODE,
			'groups' => $allGroups,
		]));

		$metadata->addPropertyConstraint('bankAccountNumber', new Assert\Length([
			'max' => Applicant::MAX_LENGTH_ACCOUNT_NUMBER,
			'groups' =>$allGroups,
		]));
	}

	public function getGroupSequence(): array
	{
		if ($this->lowIncomeEvidence === 'Other') {
			return [self::GROUP_LOW_INCOME_OTHER];
		} 

		return [self::GROUP_DEFAULT];
	}
}