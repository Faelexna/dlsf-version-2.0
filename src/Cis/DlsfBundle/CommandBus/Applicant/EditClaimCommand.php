<?php

namespace Cis\DlsfBundle\CommandBus\Applicant;

use App\Entity\Dlsf\Claim;
use App\Entity\Dlsf\Applicant;
use App\Entity\User;
use Petroc\Component\CommandBus\Command;
use Petroc\Component\Util\AssertTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class EditClaimCommand extends Command
{
	use AssertTrait;

	public $category;
	public $amount;
	public $approvedAmount;
	public $paymentType;
	public $paymentReference;
	public $notes;
        
	private $claim;
	private $applicant;
	private $user;
	
    public function __construct($object, User $user)
    {
		$this->user = $user;

		$this->assertInstanceOf($object, [Claim::class, Applicant::class]);
		if ($object instanceof Applicant) {
			$this->applicant = $object;
			return;
		}

        $this->claim = $object;
		$this->applicant = $this->claim->getApplicant();
        $this->mapData($object);
    }
    
    public function getClaim()
    {
        return $this->claim;
    }
	
	public function getApplicant()
	{
		return $this->applicant;
	}

	public function getUser()
	{
		return $this->user;
	}

	public static function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('notes', new Assert\Length([
			'max' => Claim::MAX_LENGTH_NOTES,
		]));

		$metadata->addPropertyConstraint('paymentReference', new Assert\Length([
			'max' => Claim::MAX_LENGTH_PAYMENT_REFERENCE,
		]));
	}


}