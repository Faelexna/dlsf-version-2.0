<?php

namespace Cis\DlsfBundle\CommandBus\Applicant;

use App\Entity\Dlsf\Claim;
use Petroc\Component\CommandBus\HandlerInterface;
use Petroc\Component\Helper\Orm;

class EditClaimHandler implements HandlerInterface
{
	private $orm;

	public function __construct(Orm $orm)
	{
		$this->orm = $orm;
	}
	
    public function handle(EditClaimCommand $command)
    {
		$applicant = $command->getApplicant();
		$claim = $command->getClaim();

		if (null === $claim) {
			$claim = new Claim(
				$applicant,
				$command->category,
				$command->getUser()
			);

			$this->orm->persist($claim);
		}
		
		$claim->setCategory($command->category);
		$claim->setAmount($command->amount);
		if ($claim->getApprovedAmount() === 0 && $command->approvedAmount !== 0) {
			$claim->approve($command->approvedAmount, $command->getUser());
		} else {
			$claim->setApprovedAmount($command->approvedAmount);
		}
		$claim->setPaymentType($command->paymentType);
		$claim->setPaymentReference($command->paymentReference);
		$claim->setNotes($command->notes);
    }
}