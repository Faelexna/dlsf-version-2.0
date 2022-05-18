<?php

namespace Cis\TravelBundle\CommandBus\Card;

use Petroc\Component\CommandBus\HandlerInterface;
use Petroc\Component\Helper\Orm;

class DeleteCardHandler implements HandlerInterface
{
    private $orm;
    
    public function __construct(Orm $orm)
    {
        $this->orm = $orm;
    }
    
    public function handle(DeleteCardCommand $command)
    {
        $orm = $this->orm;
        $card = $command->getCard();
        $term = $card->getTerm();
        $application = $term->getApplication();
        $claim = $application->getDlsfClaim();
        
        $orm->remove($card);
        $orm->flush();
        
        if($term->getNumberOfActiveTermriders() < 1 and $claim)
        {
            foreach($application->getTerms() as $term) {
                if($term->getNumberOfActiveTermriders() === 0) {
                    $term->setRate(0);
                }
            }
            $orm->flush();
            $total = 0;
            foreach($application->getTerms() as $countTerm) 
            {
                $total += $countTerm->getRate();
            }
            if($total < 1) {
                $orm->remove($claim);
                $application->setDlsfClaim(null);
            } else {
                $claim->setAmount($total);
                $claim->setApprovedAmount($total);
            }
        }
        
        $orm->flush();
    }
}
