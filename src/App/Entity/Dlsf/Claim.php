<?php

namespace App\Entity\Dlsf;

use App\Entity\User;
use DateTime;

class Claim
{
    const PAYMENT_TYPE_BUDGET_TRANSFER = 'Budget Transfer';
    const PAYMENT_TYPE_BACS = 'BACs';
    const PAYMENT_TYPE_VOUCHER = 'Voucher';

    const MAX_LENGTH_PAYMENT_REFERENCE = 20;
    const MAX_LENGTH_NOTES = 1000;
    const MAX_LENGTH_AUDIT_NOTES = 500;

    private $id;
    private $createdOn;
    private $addedByUser;
    private $approvedByUser;
    private $approved;
    private $approvedAmount;
    private $approvedDate;
    private $amount;
    private $paymentType;
    private $paymentReference;
    private $notes;
    private $chequeRequest;
    private $paidAmount;
    private $paidDate;
    private $order;
    private $addedByTravelSystem;
    private $chequeCollected;
    private $attendanceWhenPaid;
    private $auditNotes;
    private $category;
    private $applicant;
    private $deletedOn;
    
    public function __construct(Applicant $applicant, Category $category, User $user)
    {
        $this->createdOn = new DateTime();
        $this->applicant = $applicant;
        $this->category = $category;
        $this->addedByUser = $user;
        
        $this->approved = false;
        $this->addedByTravelSystem = false;
        $this->chequeCollected = false;        
        
        $applicant->addClaim($this);
        $category->addClaim($this);
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

    public function isApproved()
    {
        return true === $this->approved;
    }

    public function getApprovedAmount()
    {
        return $this->approvedAmount;
    }

    public function getApprovedDate()
    {
        return $this->approvedDate;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getPaymentType()
    {
        return $this->paymentType;
    }

    public function getPaymentReference()
    {
        return $this->paymentReference;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function getPaidAmount()
    {
        return $this->paidAmount;
    }

    public function getPaidDate()
    {
        return $this->paidDate;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getAddedByTravelSystem()
    {
        return $this->addedByTravelSystem;
    }

    public function getChequeCollected()
    {
        return $this->chequeCollected;
    }

    public function getAttendanceWhenPaid()
    {
        return $this->attendanceWhenPaid;
    }

    public function getAuditNotes()
    {
        return $this->auditNotes;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getApplicant()
    {
        return $this->applicant;
    }
    
    public function getDeletedOn()
    {
        return $this->deletedOn;
    }

    public function setAddedByUser($addedByUser)
    {
        $this->addedByUser = $addedByUser;
        return $this;
    }

    public function setApprovedByUser($approvedByUser)
    {
        $this->approvedByUser = $approvedByUser;
        return $this;
    }

    public function setApproved($approved)
    {
        $this->approved = $approved;
        return $this;
    }

    public function setApprovedAmount($approvedAmount)
    {
        $this->approvedAmount = $approvedAmount;
        return $this;
    }

    public function setApprovedDate($approvedDate)
    {
        $this->approvedDate = $approvedDate;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
        return $this;
    }

    public function setPaymentReference($paymentReference)
    {
        $this->paymentReference = $paymentReference;
        return $this;
    }

    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this;
    }

    public function setPaidAmount($paidAmount)
    {
        $this->paidAmount = $paidAmount;
        return $this;
    }

    public function setPaidDate($paidDate)
    {
        $this->paidDate = $paidDate;
        return $this;
    }

    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    public function setAddedByTravelSystem($addedByTravelSystem)
    {
        $this->addedByTravelSystem = $addedByTravelSystem;
        return $this;
    }

    public function setChequeCollected($chequeCollected)
    {
        $this->chequeCollected = $chequeCollected;
        return $this;
    }

    public function setAttendanceWhenPaid($attendanceWhenPaid)
    {
        $this->attendanceWhenPaid = $attendanceWhenPaid;
        return $this;
    }

    public function setAuditNotes($auditNotes)
    {
        $this->auditNotes = $auditNotes;
        return $this;
    }

    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    public function setApplicant($applicant)
    {
        $this->applicant = $applicant;
        return $this;
    }    
    
    public function getChequeRequest()
    {
        return $this->chequeRequest;
    }

    public function setChequeRequest($chequeRequest)
    {
        $this->chequeRequest = $chequeRequest;
        return $this;
    }
    
    public function approve($amount, User $user)
    {
        $this->approvedAmount = $amount;
        $this->approvedDate = new DateTime();
        $this->approved = true;
        $this->approvedByUser = $user;
        return $this;
    }

    public function markAsDeleted()
    {
        $this->deletedOn = new DateTime();
        return $this;
    }
}
