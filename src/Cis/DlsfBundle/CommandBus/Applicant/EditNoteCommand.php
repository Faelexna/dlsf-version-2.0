<?php

namespace Cis\DlsfBundle\CommandBus\Applicant;

use App\Entity\Dlsf\Note;
use App\Entity\Dlsf\Applicant;
use App\Entity\User;
use Petroc\Component\CommandBus\Command;
use Petroc\Component\Util\AssertTrait;

class EditNoteCommand extends Command
{
    use AssertTrait;

	public $notes;
        
	private $note;
    private $applicant;
    private $user;
	
    public function __construct($object, User $user = null)
    {
        if ($object instanceof Applicant) {
            $this->applicant = $object;
            $this->user = $user;
            return;
        }

        $this->note = $object;
        $this->applicant = $this->note->getApplicant();
        $this->mapData($object);
    }
    
    public function getNote()
    {
        return $this->note;
    }
	
    public function getApplicant()
    {
        return $this->applicant;
    }

    public function getUser()
    {
        return $this->user;
    }
}