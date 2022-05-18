<?php

namespace App\Entity\Dlsf;

use App\Entity\Misc\GeneralCode;
use DateTime;
use Petroc\Component\Helper\Orm;

class EvidenceType 
{
    const EVIDENCE_TYPE_CODE = 'A';
    const LOW_INCOME_TYPE_CODE = 'L';

    private $id;
    private $createdOn;
    private $description;
    private $ordering;
    private $type;
    private $category;
    private $academicYear;
    private $deletedOn;
    
    public function __construct($description, $academicYear, $lowIncome, $ordering)
    {
        $this->createdOn = new DateTime();
        $this->description = $description;
        $this->academicYear = $academicYear;
        
        if (true === $lowIncome) {
            $this->type = self::LOW_INCOME_TYPE_CODE;
        } else {
            $this->type = self::EVIDENCE_TYPE_CODE;
        }   

        $this->ordering = $ordering;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getCreatedOn()
    {
        return $this->createdOn;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function getAcademicYear()
    {
        return $this->academicYear;
    }
    
    public function getOrdering()
    {
        return $this->ordering;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getType()
    {
        return $this->type;
    }
    
    public function getDeletedOn()
    {
        return $this->deletedOn;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    
    public function setAcademicYear($academicYear)
    {
        $this->academicYear = $academicYear;
        return $this;
    }
    
    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;
        return $this;
    }

    public function setCategory(GeneralCode $category)
    {
        $this->category = $category;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function markAsDeleted()
    {
        $this->deletedOn = new DateTime();
        return $this;
    }
}
