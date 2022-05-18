<?php

namespace Cis\DlsfBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Cis\FinanceBundle\Model\Transfer;
use App\Entity\User;

class TransferRequest extends Transfer
{
    const BATCH_REFERENCE_PREFIX = 'ATRN';

    private $id;
    private $createdOn;
    private $addedByUser;
    private $approvedByUser;
    private $academicYear;
    
    public function __construct(User $user, $description, $reference = null, $date = null, $academicYear)
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

    public function getApprovedByUser()
    {
        return $this->approvedByUser;
    }

    public function getAcademicYear()
    {
        return $this->academicYear;
    }
    
    public function approveTransfer(User $user, $reference)
    {
        $this->reference = $reference;
        $this->approvedByUser = $user;
        return $this;
    }
}
