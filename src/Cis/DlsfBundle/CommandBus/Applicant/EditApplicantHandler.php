<?php

namespace Cis\DlsfBundle\CommandBus\Applicant;

use Petroc\Component\CommandBus\HandlerInterface;
use App\Entity\Dlsf\Note;
use DateTime;

class EditApplicantHandler implements HandlerInterface
{
    public function handle(EditApplicantCommand $command)
    {
		$applicant = $command->getApplicant();
		
		$applicant->setAgeCategory($command->ageCategory);
		$applicant->setFinanciallyIndependent($command->financiallyIndependent);
		$applicant->setLiveWithPartner($command->liveWithPartner);
		$applicant->setNumberOfDependents($command->numberOfDependents);
		$applicant->setTwentyFourPlusEvidenceSeen($command->twentyFourPlusEvidenceSeen);
		$applicant->setParentBenefits($command->parentBenefits);
		$applicant->setShowOnEnhancedReport($command->showOnEnhancedReport);
		$applicant->setNotOnMainstreamProgramme($command->notOnMainstreamProgramme);
		$applicant->setHouseholdIncome($command->householdIncome);
		$applicant->setLowIncomeEvidence($command->lowIncomeEvidence);
		$applicant->setLowIncomeEvidenceOther($command->lowIncomeEvidenceOther);
		if (isset($command->evidenceType)&&!empty($command->evidenceType)) {
			if ($applicant->isEvidenceSeen() === false) {
				$applicant->setEvidenceType($command->evidenceType);
				$applicant->setEvidenceSeen(true);
				$applicant->setEvidenceSeenUser($command->getUser());
				$applicant->setEvidenceSeenDate(new DateTime());
			} elseif ($applicant->getEvidenceType() !== $command->evidenceType) {
				$note = new Note(
					$applicant, 
					$command->getUser(), 
					'Evidence type changed from '.$applicant->getEvidenceType(). ' to '.$command->evidenceType
				);
				$applicant->setEvidenceType($command->evidenceType);
				$applicant->setEvidenceSeenUser($command->getUser());
				$applicant->setEvidenceSeenDate(new DateTime());
			}
		}
		$applicant->setBankAccountHolder($command->bankAccountHolder);
		$applicant->setBankName($command->bankName);
		$applicant->setBankSortCode($command->bankSortCode);
		$applicant->setBankAccountNumber($command->bankAccountNumber);
    }
}