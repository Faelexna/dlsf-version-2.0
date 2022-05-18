<?php

namespace Cis\DlsfBundle\CommandBus\Admin;

use App\Entity\Dlsf\EvidenceType;
use Petroc\Component\CommandBus\HandlerInterface;
use Petroc\Component\Helper\Orm;

class EditEvidenceTypeHandler implements HandlerInterface
{
    private $orm;

    public function __construct(Orm $orm)
    {
        $this->orm = $orm;
    }

    public function handle(EditEvidenceTypeCommand $command)
    {
        $evidenceType = $command->getEvidenceType();

        if (null === $evidenceType) {
            $nextOrder = $this->orm
                ->createQueryBuilder('e')
                ->select('count(e) + 1')
                ->where('e.academic_ear = :ayear')
                ->andWhere('e.type = :type')
                ->andWhere('e.deleted_on is null')
                ->setParameter('ayear', $command->getAcademicYear())
                ->setParameter('type', EvidenceType::EVIDENCE_TYPE_CODE)
                ->getQuery
                ->getResult
            ;

            $evidenceType = new EvidenceType(
                $command->description,
                $command->getAcademicYear(),
                0,
                $nextOrder
            );

            $this->orm->persist($evidenceType);
        } else {
            $evidenceType->setDescription($command->description);
        }
        if (null !== $command->category) {
            $evidenceType->setCategory($command->category);
        }
    }
}
