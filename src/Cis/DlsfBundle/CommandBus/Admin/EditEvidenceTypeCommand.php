<?php

namespace Cis\DlsfBundle\CommandBus\Admin;

use App\Entity\Dlsf\EvidenceType;
use App\Entity\User;
use Petroc\Component\CommandBus\Command;

class EditEvidenceTypeCommand extends Command
{
    public $description;
    public $type;
    public $category;

    private $evidenceType;
    private $academicYear;
    private $user;

    public function __construct($object, User $user)
    {
        $this->user = $user;

        if ($object instanceof EvidenceType) {
            $this->evidenceType = $object;
            $this->academicYear = $object->getAcademicYear();
            $this->mapData($object);
        } else {
            $this->academicYear = $object;
        }
    }

    public function getEvidenceType()
    {
        return $this->evidenceType;
    }

    public function getAcademicYear()
    {
        return $this->academicYear;
    }

    public function getUser()
    {
        return $this->user;
    }
}
