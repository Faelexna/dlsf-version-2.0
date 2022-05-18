<?php

namespace Cis\TravelBundle\CommandBus\Report;

use Petroc\Component\CommandBus\SelfHandlingCommand;

class FinalDaysCommand extends SelfHandlingCommand
{
    protected $cards;
    private $site;
    public $details;

    public function __construct(array $cards, $site)
    {
        $this->site = $site;
        $this->cards = $cards;

        foreach ($cards as $card) {
            $this->details[$card->getId()] = [
                'finalDay' => null,
                'contractor' => $card->getContractor()
            ];
        }
    }

    public function getSite()
    {
        return $this->site;
    }

    public function getCards()
    {
        return $this->cards;
    }

    public function handle()
    {
        foreach ($this->cards as $card) {
            $id = $card->getId();
            if (!isset($this->details[$id]['finalDay'])) {
                continue;
            }

            $finalDay = $this->details[$id]['finalDay'];

            if (!empty($finalDay)) {
                $card->setFinalDays($finalDay);
            }
            $term = $card->getTerm();
            if (!empty($term->getDlsfClaim())) {
                $cost = $term->getTotalDailyCost();
                $claim = $term->getDlsfClaim();
                $claim->setAmount($cost);
                $claim->setApprovedAmount($cost);

                if($claim->getAmount() === null or $claim->getApprovedAmount() === null) {
                    throw new InvalidArgumentException('DLSF cost error - Final Days');
                }

            }
        }
    }
}
