<?php

namespace Cis\DlsfBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\User;
use Cis\FinanceBundle\Model\Transfer;

class Income extends Transfer
{
    const BATCH_REFERENCE_PREFIX = 'AINC';

    private $id;
    private $createdOn;
    private $addedByUser;
    private $academicYear;

    public function __construct($description, $date, $reference, User $user, $academicYear)
    {
        $batchReference = $this::BATCH_REFERENCE_PREFIX . date('ymd');

        parent::__construct($description, $batchReference, $reference, $date);
        $this->createdOn = new DateTime();
        $this->details = new ArrayCollection();
        $this->addedByUser = $user;
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

    public function getAddedByUser()
    {
        return $this->addedByUser;
    }

    public function getAcademicYear()
    {
        return $this->academicYear;
    }
}
