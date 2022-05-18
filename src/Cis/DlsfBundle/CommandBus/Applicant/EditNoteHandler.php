<?php

namespace Cis\DlsfBundle\CommandBus\Applicant;

use App\Entity\Dlsf\Note;
use Petroc\Component\CommandBus\HandlerInterface;
use Petroc\Component\Helper\Orm;

class EditNoteHandler implements HandlerInterface
{
    private $orm;

    public function __construct(Orm $orm)
    {
      $this->orm = $orm;
    }

    public function handle(EditNoteCommand $command)
    {
		$note = $command->getNote();
    $applicant = $command->getApplicant();

    if (null === $note) {
      $note = new Note(
        $applicant,
        $command->getUser(),
        $command->notes
      );

      $this->orm->persist($note);
      return;
    }
		
		$note->setNote($command->notes);
    }
}