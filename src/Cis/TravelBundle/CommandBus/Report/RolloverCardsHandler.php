<?php

namespace Cis\TravelBundle\CommandBus\Report;

use Cis\TravelBundle\Entity\Card;
use Cis\TravelBundle\Entity\Term;
use Cis\TravelBundle\Messenger\TravelMessenger;
use Cis\TravelBundle\Util\CalculatorUtil;
use Petroc\Component\CommandBus\HandlerInterface;
use Petroc\Component\Helper\Orm;
use Datetime;

class RolloverCardsHandler implements HandlerInterface
{
    private $messenger;
    private $util;
    private $orm;
            
    public function __construct(TravelMessenger $messenger, CalculatorUtil $util, Orm $orm)
    {
        $this->messenger = $messenger;
        $this->util = $util;
        $this->orm = $orm;
    }
    
    public function handle(RolloverCardsCommand $command)
    {
        $details = $command->details;

        $toActivate = [];

        foreach ($details as $detail) {
             if($detail['term'] === Term::AUTUMN and $detail['selected'] === true) {
                $term = $this->orm->getRepository(Term::class)->findApprovedTerm($detail['application'], Term::SPRING);
                $toActivate[] = [
                    'details' => $detail,
                    'term' => $term
                ];
             }
             if($detail['term'] === Term::SPRING and $detail['selected'] === true) {
                $term = $this->orm->getRepository(Term::class)->findApprovedTerm($detail['application'], Term::SUMMER);
                $toActivate[] = [
                    'details' => $detail,
                    'term' => $term
                ];
             }
        }
        
        if (count($toActivate) > 0) {
            foreach($toActivate as $card) {
                $this->createCard($card['term'], $card['details']);
            }
        }
       
    }
    
    public function createCard($term, $detail) 
    {
        $oldCard = $detail['card'];

        if ($term->isApproved()) {
            
            $oldCard->setRolledOver(true);
            
            $card = new Card(
                $term, 
                $oldCard->getContractor(),
                $this->util->calculateExpiry($term),
                $detail['rate']
            );

            $card->setPickupPoint($oldCard->getPickupPoint());
            $card->setDropoffPoint($oldCard->getDropoffPoint());
            $card->setMonday($oldCard->isMonday());
            $card->setTuesday($oldCard->isTuesday());
            $card->setWednesday($oldCard->isWednesday());
            $card->setThursday($oldCard->isThursday());
            $card->setFriday($oldCard->isFriday());         
            $card->setInitialDays($detail['initialDays']);
            $card->setStatus(Card::INCOMPLETE_STATUS);

            if($oldCard->getContractor()->isCouncil() or $oldCard->getContractor()->isTrain()) {
                $card->issue($oldCard->getReference());
                $card->setCollectedOn(new DateTime);
                $card->setOrderedOn(new DateTime);
            }
            
            if($term->getDlsfClaim() !== null and !$oldCard->getContractor()->isTermly()) {
                $cost = $term->getTotalDailyCost();
                $claim = $term->getDlsfClaim();
                $claim->setAmount($cost);
                $claim->setApprovedAmount($cost);
            }
        }
    }
}
