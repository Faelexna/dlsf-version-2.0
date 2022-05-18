<?php

namespace App\Repository\Dlsf;

class ClaimCriteria
{
    private $academicYear;
    public $paymentType;
    public $ageCategory;
    public $fundingType;
    public $site;
    public $category;

    public function __construct($academicYear)
    {
        $this->academicYear = $academicYear;
    }
    
    public function setAcademicYear($academicYear)
    {
        $this->academicYear = $academicYear;
        return $this;
    }
    
    public function getAcademicYear()
    {
        return $this->academicYear;
    }
}
