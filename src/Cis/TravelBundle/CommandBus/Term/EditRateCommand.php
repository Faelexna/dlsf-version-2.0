<?php

namespace Cis\TravelBundle\CommandBus\Term;

use Cis\TravelBundle\Entity\Term;
use Petroc\Component\CommandBus\SelfHandlingCommand;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;

class EditRateCommand extends SelfHandlingCommand
{
    private $term;
    public $rate;
    public $chargeRequired;
    
    public function __construct(Term $term)
    {   
        $this->term = $term;
        $this->rate = $term->getRate();
        $this->chargeRequired = $term->isChargeRequired();
    }

    public function getTerm()
    {
        return $this->term;
    }
        
    public function handle()
    {
        $term = $this->term;
        $term->setRate($this->rate);
        
        if(!$this->chargeRequired) {
            $this->term->noChargeRequired();
        }
        
        if($this->chargeRequired) {
            $this->term->chargeRequired();
        }
        
        $application = $term->getApplication();
        
        $term->lock();
        
        
        if (!empty($application->getDlsfClaim())) {

            $cost = 0;

            foreach ($application->getTerms() as $term) {
                $cost += $term->getRate();
            }

            $claim = $application->getDlsfClaim();
            $claim->setAmount($cost);
            $claim->setApprovedAmount($cost);

            if($claim->getAmount() === null or $claim->getApprovedAmount() === null) {
                throw new InvalidArgumentException('DLSF cost error - Change rate');
            }
        
        }
    }
    
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new Callback([
            'callback' => 'validateChargeRequired'
        ]));
    }
   
    public function validateChargeRequired(ExecutionContextInterface $context)
    {
          if($this->chargeRequired) {
            return;
        }
        
        if ($this->rate > 0) {
            $context
                ->buildViolation('Rate must be £0 if there is no charge required.')
                ->atPath('rate')
                ->addViolation()
            ;
            return;
        }
    }
    
}
