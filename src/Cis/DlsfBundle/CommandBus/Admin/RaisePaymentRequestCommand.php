<?php

namespace Cis\DlsfBundle\CommandBus\Admin;

use App\Entity\User;
use Petroc\Component\CommandBus\Command;

class RaisePaymentRequestCommand extends Command
{
    private $user;
    private $academicYear;
    private $claims;

    public $ageCategory;
    public $fundingType;
    public $site;
    public $category;

    public function __construct(User $user, $academicYear)
    {
        $this->user = $user;
        $this->academicYear = $academicYear;
        $this->claims = [];
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getAcademicYear()
    {
        return $this->academicYear;
    }

    public function getClaims()
    {
        return $this->claims;
    }

    public function setClaims($claims)
    {
        $this->claims = $claims;
    }
}
