<?php

namespace App\Entity\Dlsf;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class Category
{
    const TRAVEL_CATEGORY = 'Travel';
    
    private $id;
    private $createdOn;
    private $name;
    private $academicYear;
    private $internalOnly;
    private $mealType;
    private $claims;
    private $deletedOn;
    private $subDetail;
    private $financeOnly;

    public function __construct($name, $academicYear)
    {
        $this->claims = new ArrayCollection();

        $this->createdOn = new DateTime();
        $this->name = $name;
        $this->academicYear = $academicYear;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAcademicYear()
    {
        return $this->academicYear;
    }
    
    public function isInternalOnly()
    {
        return true === $this->internalOnly;
    }

    public function isFinanceOnly()
    {
        return true === $this->financeOnly;
    }

    public function getMealType()
    {
        return $this->mealType;
    }

    public function hasClaims()
    {
        return false === count($this->claims);
    }

    public function getClaims()
    {
        return $this->claims;
    }

    public function getDeletedOn()
    {
        return $this->deletedOn;
    }

    public function getSubDetail()
    {
        return $this->subDetail;
    }

    public function setInternalOnly($internalOnly)
    {
        $this->internalOnly = $internalOnly;
        return $this;
    }

    public function setFinanceOnly($financeOnly)
    {
        $this->financeOnly = $financeOnly;
        return $this;
    }

    public function setMealType($mealType)
    {
        $this->mealType = $mealType;
        return $this;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setAcademicYear($academicYear)
    {
        $this->academicYear = $academicYear;
        return $this;
    }

    public function markAsDeleted()
    {
        $this->deletedOn = new DateTime();
        return $this;
    }

    public function addClaim(Claim $claim)
    {
        $this->claims[] = $claim;
        return $this;
    }

    public function setSubDetail($subDetail)
    {
        $this->subDetail = $subDetail;
        return $this;
    }
}
