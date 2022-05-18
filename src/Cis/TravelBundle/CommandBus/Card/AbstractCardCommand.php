<?php

namespace Cis\TravelBundle\CommandBus\Card;

use Cis\TravelBundle\Entity\Card;
use Cis\TravelBundle\Entity\Contractor;
use Cis\TravelBundle\Validator\Constraint\CardRate;
use Cis\TravelBundle\Validator\Constraint\Contractor as ContractorConstraint;
use Cis\TravelBundle\Validator\Constraint\InUseCard;
use Cis\TravelBundle\Validator\Constraint\UniqueCardReference;
use Petroc\Component\CommandBus\SelfHandlingCommand;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\GroupSequenceProviderInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

abstract class AbstractCardCommand extends SelfHandlingCommand implements GroupSequenceProviderInterface
{
    const DEFAULT_GROUP = 'default';
    const EDIT_GROUP = 'edit';
    const EDIT_DAILY_RATE_GROUP = 'edit_daily_rate';
    const ADD_GROUP = 'add';
    const REPLACE_GROUP = 'replace';
    
    const ISSUED_GROUP = 'issued';
    const NOT_ISSUED_GROUP = 'not_issued';
    const NO_REFERENCE_GROUP = 'no_reference';
    const NO_REFERENCE_ADD_GROUP = 'no_reference_add';
    const REFERENCE_GROUP = 'reference';
    const FINAL_DAYS_GROUP = 'final_days';
    const TERMLY_GROUP = 'termly';
    const NON_TERMLY_GROUP = 'non_termly';
    const NO_REFERENCE_REPLACE_GROUP = 'no_reference_replace';
    
    protected $card;    
    public $contractor;
    public $rate;
    public $additionalFee;
    public $monday;
    public $tuesday;
    public $wednesday;
    public $thursday;
    public $friday;
    public $notes;
    public $issuedOn;
    public $orderedOn;
    public $postedOn;
    public $collectedOn;
    public $reference;
    public $pickupPoint;
    public $status;
    public $expiresOn;
    public $dropoffPoint;
    public $initialDays;
    public $finalDays;

    
    abstract public function getTerm();
   
    public function handle()
    {
        $this->card->setRate($this->rate);
        $this->card->setAdditionalFee($this->additionalFee);
        $this->card->setMonday($this->monday);
        $this->card->setTuesday($this->tuesday);
        $this->card->setWednesday($this->wednesday);
        $this->card->setThursday($this->thursday);
        $this->card->setFriday($this->friday);
        $this->card->setNotes($this->notes);
        if ($this->contractor->isStagecoachTermrider() or $this->contractor->isDartline() or $this->contractor->isDdcDaily()) {
            $this->card->setReference($this->reference);
        } else {
            $this->card->setReference($this->card->getId());
        }
        $this->card->setPickupPoint($this->pickupPoint);
        $this->card->setIssuedOn($this->issuedOn);
        $this->card->setOrderedOn($this->orderedOn);
        $this->card->setPostedOn($this->postedOn);
        $this->card->setCollectedOn($this->collectedOn);   
        $this->card->setDropoffPoint($this->dropoffPoint);
        $this->card->setInitialDays($this->initialDays);
        $this->card->setFinalDays($this->finalDays);
        
        $term = $this->card->getTerm();
        
        if (!empty($term->getDlsfClaim()) and !$this->contractor->isTermly()) {

            $cost = $term->getTotalDailyCost();

            $claim = $term->getDlsfClaim();
            $claim->setAmount($cost);
            $claim->setApprovedAmount($cost);

            if($claim->getAmount() === null or $claim->getApprovedAmount() === null) {
                throw new InvalidArgumentException('DLSF cost error - Edit/Add');
            }
        
        }
        
    }
    
    public function getCard()
    {
        return $this->card;
    }
    
    abstract protected function getGroup();
    
    public function getGroupSequence()
    {
        $groups = [
            self::DEFAULT_GROUP,
            $this->getGroup()
        ];
        
        if($contractor = $this->contractor) {
            if ($contractor->isStagecoachTermrider()) {
                $groups[] = self::TERMLY_GROUP;
            }
            if (!$contractor->isTermly()) {
                $groups[] = self::NON_TERMLY_GROUP;
            } 
            if (!$contractor->isStagecoachTermrider() and !in_array($this->status, [Card::ISSUED_STATUS, Card::INCOMPLETE_STATUS]) and $this->getGroup() === self::EDIT_GROUP) {
                $groups[] = self::FINAL_DAYS_GROUP;
            }
            if (!in_array($contractor->getId(), array(Contractor::STAGECOACH_TERMRIDER_BARNSTAPLE_ID, Contractor::DARTLINE_TIVERTON_ID, Contractor::DDC_DAILY_TIVERTON_ID))) {
                $groups[] = self::NO_REFERENCE_GROUP;
            }
            if (!in_array($contractor->getId(), array(Contractor::STAGECOACH_TERMRIDER_BARNSTAPLE_ID, Contractor::DARTLINE_TIVERTON_ID, Contractor::DDC_DAILY_TIVERTON_ID)) and $this->getGroup() === self::ADD_GROUP) {
                $groups[] = self::NO_REFERENCE_ADD_GROUP;
            } 
            if (!in_array($contractor->getId(), array(Contractor::STAGECOACH_TERMRIDER_BARNSTAPLE_ID, Contractor::DARTLINE_TIVERTON_ID, Contractor::DDC_DAILY_TIVERTON_ID)) and $this->getGroup() === self::REPLACE_GROUP) {
                $groups[] = self::NO_REFERENCE_REPLACE_GROUP;
            } 
        }
        
        return [$groups];
    }
    
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        
        $metadata->setGroupSequenceProvider(true);
        $metadata->addPropertyConstraint('reference', new Assert\Blank([
            'groups' => [
                self::NO_REFERENCE_ADD_GROUP,
                self::NO_REFERENCE_REPLACE_GROUP
            ],
            'message' => 'This should be blank, as it will be generated later.'
        ]));
        $metadata->addPropertyConstraint('initialDays', new Assert\NotBlank([
            'groups' => [
                self::NON_TERMLY_GROUP
            ]
        ]));
        $metadata->addPropertyConstraint('initialDays', new Assert\Blank([
            'groups' => [
                self::TERMLY_GROUP
            ]
        ]));
        $metadata->addPropertyConstraint('finalDays', new Assert\Blank([
            'groups' => [
                self::TERMLY_GROUP
            ]
        ]));
        $metadata->addPropertyConstraint('finalDays', new Assert\Blank([
            'groups' => [
                self::ADD_GROUP
            ]
        ]));
        $metadata->addPropertyConstraint('finalDays', new Assert\NotBlank([
            'groups' => [
                self::FINAL_DAYS_GROUP
            ],
            'message' => 'You must enter the amount of days the learner has used this card for during this term.'
        ]));
        $metadata->addPropertyConstraint('expiresOn', new Assert\NotNull([
            'groups' => self::DEFAULT_GROUP
        ]));
        $metadata->addPropertyConstraint('contractor', new Assert\NotNull([
            'groups' => self::DEFAULT_GROUP
        ]));
        $metadata->addConstraint(new CardRate([
            'groups' => self::DEFAULT_GROUP
        ]));
        $metadata->addConstraint(new InUseCard([
            'groups' => [self::EDIT_GROUP, self::ADD_GROUP]
        ]));
        $metadata->addConstraint(new UniqueCardReference([
            'groups' => self::TERMLY_GROUP
        ]));
        $metadata->addPropertyConstraint('notes', new Assert\Length([
            'max' => Card::NOTES_MAX_LENGTH,
            'groups' => self::DEFAULT_GROUP
        ]));
        $metadata->addPropertyConstraint('reference', new Assert\Length([
            'max' => Card::REFERENCE_MAX_LENGTH,
            'groups' => self::DEFAULT_GROUP
        ]));
        $metadata->addPropertyConstraint('pickupPoint', new Assert\Length([
            'max' => Card::PICKUP_POINT_MAX_LENGTH,
            'groups' => self::DEFAULT_GROUP
        ]));
        $metadata->addPropertyConstraint('dropoffPoint', new Assert\Length([
            'max' => Card::DROPOFF_POINT_MAX_LENGTH,
            'groups' => self::DEFAULT_GROUP
        ]));
        $metadata->addConstraint(new ContractorConstraint([
            'groups' => self::DEFAULT_GROUP
        ]));
        $metadata->addConstraint(new Callback([
            'callback' => 'validateNeverIssued',
            'groups' => [self::EDIT_GROUP]
        ]));
        $metadata->addConstraint(new Callback([
            'callback' => 'validateReferenceNoChange',
            'groups' => [self::EDIT_GROUP]
        ]));
        $metadata->addConstraint(new Callback([
            'callback' => 'validateStatus',
            'groups' => [self::EDIT_GROUP, self::ADD_GROUP]
        ]));
        $metadata->addConstraint(new Callback([
            'callback' => 'validateAdditionalFee',
            'groups' => self::DEFAULT_GROUP
        ]));
    }
    
    public function validateStatus(ExecutionContextInterface $context)
    {
        if(!$this->contractor) {
            return;
        }
        if ($this->contractor->isStagecoachTermrider() and $this->status === Card::AMENDED_STATUS) {
            $context
                ->buildViolation('Amended can not be used on Stagecoach Termrider')
                ->atPath('status')
                ->addViolation()
            ;
            return;
        }
    }
    
    public function validateNeverIssued(ExecutionContextInterface $context)
    {
        if(!$this->contractor) {
            return;
        }
        
        if ($this->contractor->isTermly() and $this->getTerm()->getRate() > 0 and $this->card->isNeverIssued() and $this->status === Card::NEVER_ISSUED_STATUS) {
            $context
                ->buildViolation('If this card has never been issued, the term rate should be £0')
                ->atPath('termRate')
                ->addViolation()
            ;
            return;
        }
    }
    
    public function validateReferenceNoChange(ExecutionContextInterface $context)
    {
        if(!$this->contractor) {
            return;
        }
        
        $contractor = $this->contractor;
        if ($this->reference !== $this->card->getReference() and (!in_array($contractor->getId(), array(Contractor::STAGECOACH_TERMRIDER_BARNSTAPLE_ID, Contractor::DARTLINE_TIVERTON_ID)))) {
            $context
                ->buildViolation('This Reference can not be changed.')
                ->atPath('reference')
                ->addViolation()
            ;
            return;
        }
    }
    
    public function validateAdditionalFee(ExecutionContextInterface $context)
    {
        if(!$this->contractor) {
            return;
        }

        if ($this->additionalFee and $this->contractor->isStagecoachTermrider()) {
            $context
                ->buildViolation('Additional Fee only for daily cards.')
                ->atPath('additionalFee')
                ->addViolation()
            ;
            return;
        }
    }
}
