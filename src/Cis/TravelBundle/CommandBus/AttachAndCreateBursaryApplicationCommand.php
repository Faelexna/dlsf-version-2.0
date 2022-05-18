<?php

namespace Cis\TravelBundle\CommandBus;

use App\Entity\Dlsf\Category;
use App\Entity\Dlsf\Claim;
use App\Entity\User;
use Cis\TravelBundle\Entity\Application;
use Cis\TravelBundle\Entity\Term;
use InvalidArgumentException;
use Petroc\Component\CommandBus\SelfHandlingCommand;
use Petroc\Component\Helper\Orm;

class AttachAndCreateBursaryApplicationCommand extends SelfHandlingCommand
{        
    private $orm;
    private $user;
    private $entity;
    private $type;
    public $dlsfApplication;
    public $cost;

    public function __construct(Orm $orm, User $user, $entity, $type = null)
    {
        $this->orm = $orm;
        $this->entity = $entity;
        $this->user = $user;
        $this->type = $type;
    }
    
    public function handle()
    {          
        $entity = $this->entity;
        
        if ($entity instanceOf Application) {
            $note = $this->type.' - Termly Ticket';
        } elseif ($entity instanceOf Term) {
            $note = $entity.' - Daily Ticket';
        } else {
            throw new InvalidArgumentException('Entity not an instance of Application or Term');
        }
        
        if (!empty($entity->getDlsfClaim())) {
            throw new InvalidArgumentException('DLSF already attached.');
        }
        
        $academicYear = $this->dlsfApplication->getAcademicYear();
        
        $category = $this->orm->getRepository(Category::class)->findOneBy(['academicYear' => $academicYear,'name' => Category::TRAVEL_CATEGORY]);
        
        if($category === null) {
            throw new InvalidArgumentException('Cannot find DLSF category '.Category::TRAVEL_CATEGORY.' for '.$academicYear);
        }
  
        $claim = 
            new Claim(
                $this->dlsfApplication,
                $category,
                $this->user
            );
        
        $claim->setAmount($this->cost);
        $claim->approve($this->cost,$this->user);
        $claim->setNotes($note);
        $claim->setPaymentType(Claim::PAYMENT_TYPE_BUDGET_TRANSFER);
        $claim->setAddedByTravelSystem(true);
       
        $entity->setDlsfClaim($claim);
        
        if($claim->getAmount() === null or $claim->getApprovedAmount() === null or $claim->getPaidAmount() === null) {
            throw new InvalidArgumentException('DLSF cost error - Attach.');
        }
    }

}