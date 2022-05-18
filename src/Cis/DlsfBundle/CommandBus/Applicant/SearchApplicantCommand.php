<?php

namespace Cis\DlsfBundle\CommandBus\Applicant;

use App\Entity\Dlsf\Applicant;
use App\Repository\Dlsf\ApplicantCriteria;
use Petroc\Component\CommandBus\FilterCommandInterface;
use Petroc\Component\CommandBus\FilterCommandTrait;
use Petroc\Component\Helper\Orm;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class SearchApplicantCommand extends ApplicantCriteria implements FilterCommandInterface
{
	use FilterCommandTrait;
	
	public function handle(Orm $orm)
	{
		return $this->setResult(
			$orm->getRepository(Applicant::class)->match($this)
		);
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new Callback([
            'callback' => 'validateHouseholdIncome',
        ]));
    }
	
	public function validateHouseholdIncome(ExecutionContextInterface $context)
	{
		if($this->householdIncomeOne!==null&&$this->householdIncomeTwo!==null&&$this->householdIncomeOne>$this->householdIncomeTwo) {
			$context
                ->buildViolation('The income entered in box one is greater than that of box two')
                ->atPath('householdIncome')
                ->addViolation()
            ;
			return;
		}
	}
}