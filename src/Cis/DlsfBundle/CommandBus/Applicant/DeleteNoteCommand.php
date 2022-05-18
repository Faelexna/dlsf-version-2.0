<?php

namespace Cis\DlsfBundle\CommandBus\Applicant;

use App\Entity\Dlsf\Note;
use Petroc\Component\CommandBus\SelfHandlingCommand;

class DeleteNoteCommand extends SelfHandlingCommand
{
    private $note;

    public function __construct(Note $note)
    {
        $this->note = $note;
    }

    public function handle()
    {
        $this->note->markAsDeleted();
    }
}
