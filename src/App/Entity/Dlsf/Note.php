<?php

namespace App\Entity\Dlsf;

use App\Entity\User;
use DateTime;

class Note
{
    private $id;
    private $createdOn;
    private $addedByUser;
    private $applicant;
    private $note;
    private $deletedOn;
            
    public function __construct(Applicant $applicant, User $user, $note)
    {
        $this->createdOn = new DateTime();
        $this->applicant = $applicant;
        $this->addedByUser = $user;
        $this->note = $note;

        $applicant->addNote($this);
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
	
	public function getApplicant()
	{
		return $this->applicant;
	}
	
	public function getNote()
	{
		return $this->note;
	}
	
	public function getDeletedOn()
	{
		return $this->deletedOn;
	}

	public function isDeleted()
    {
        return null !== $this->deletedOn;
    }
	
	public function setAddedByUser($addedByUser)
	{
		$this->addedByUser = $addedByUser;
		return $this;
	}
	
	public function setApplicant($applicant)
	{
		$this->applicant = $applicant;
		return $this;
	}
	
	public function setNote($note)
	{
		$this->note = $note;
		return $this;
	}
	
	public function markAsDeleted()
	{
		$this->deletedOn = new DateTime();
		return $this;
	}
}
