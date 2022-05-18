<?php

namespace Cis\TravelBundle\CommandBus\Application;

use Cis\TravelBundle\Entity\Application;
use Cis\TravelBundle\Entity\Card;
use Cis\TravelBundle\Util\CalculatorUtil;
use InvalidArgumentException;
use Petroc\Component\CommandBus\SelfHandlingCommand;
use Petroc\Component\Helper\Orm;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class WithdrawCommand extends SelfHandlingCommand
{        
    private $application;
    private $orm;
    public $date;
    public $cardStatus;
    
    public function __construct(Orm $orm, Application $application)
    {
        $this->orm = $orm;
        $this->application = $application;
    }
    
    public function getApplication()
    {
        return $this->application;
    }

    public function handle()
    {   
        $util = new CalculatorUtil;
        
        $application = $this->application;
        $application->setStatus(Application::WITHDRAWN_STATUS);
        $application->setWithdrawnOn($this->date);
        
        
        if($this->cardStatus === Card::NEVER_ISSUED_STATUS) {
            foreach ($application->getTerms() as $term) {
                $term->setRate(0);
            }
        } else {
            $util->calculateWithdrawnTerms($application);
        }
        foreach ($application->getTerms() as $term) {
            foreach ($term->getCards() as $card) {
                if($card->isIssued() and !$card->isRolledOver()) {
                    $card->setStatus($this->cardStatus);
                }
                if ($this->date < $card->getExpiresOn()) {
                    $card->setExpiresOn($this->date);
                }
            }
            if($term->getDlsfClaim() !== null and $term->getNumberOfDailyCards() > 0) {
                $cost = $term->getTotalDailyCost();
                $claim = $term->getDlsfClaim();
                $claim->setAmount($cost);
                $claim->setApprovedAmount($cost);
            }
            if($term->getDlsfClaim() !== null and $term->getNumberOfDailyCards() < 1) {
                $claim = $term->getDlsfClaim();
                $this->orm->remove($claim);
                $term->setDlsfClaim(null);
                $term->setEstimatedCost(null);
            }
        }
        
        $cost = 0;

        foreach ($application->getTerms() as $term) {
            $cost += $term->getRate();
        }
        
        if (!empty($application->getDlsfClaim())) {
            $claim = $application->getDlsfClaim();
            $claim->setAmount($cost);
            $claim->setApprovedAmount($cost);
            
            if($claim->getAmount() === null or $claim->getApprovedAmount() === null) {
                throw new InvalidArgumentException('DLSF cost error.');
            }
        }
    }
    
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('date', new NotNull());
        $metadata->addConstraint(new Callback([
            'callback' => 'validateFinalDaysNeeded'
        ]));
        $metadata->addConstraint(new Callback([
            'callback' => 'validateStatus'
        ]));
    }

    public function validateStatus(ExecutionContextInterface $context)
    {
        $application = $this->application;
        
        $nonTermrider = false;
        
        foreach ($application->getTerms() as $term) {
            foreach ($term->getCards() as $card) {
                if ($card->isIssued() and !$card->getContractor()->isStagecoachTermrider()) {
                    $nonTermrider = true;
                }
            }
        }
        
        if ($nonTermrider === true and $this->cardStatus === Card::USED_REISSUED_STATUS) {
            $context
                ->buildViolation('Used - Then Reissued can only be used on Stagecoach Termrider')
                ->atPath('cardStatus')
                ->addViolation()
            ;
            return;
        }
        if ($nonTermrider === true and $this->cardStatus === Card::FAULTY_REISSUED_STATUS) {
            $context
                ->buildViolation('Faulty - Then Reissued can only be used on Stagecoach Termrider')
                ->atPath('cardStatus')
                ->addViolation()
            ;
            return;
        }
    }
    
    public function validateFinalDaysNeeded(ExecutionContextInterface $context)
    {
        $application = $this->application;
        
        foreach ($application->getTerms() as $term) {
            foreach ($term->getCards() as $card) {

                if($card->isIssued() and !$card->getContractor()->isTrain() and !$card->getContractor()->isStagecoachTermrider() and $card->getFinalDays() === null) {
                $context
                    ->buildViolation('This Application has a card which need final days adding.')
                    ->atPath('date')
                    ->addViolation()
                ;
                return;
                }
            }
        }
    }
}
