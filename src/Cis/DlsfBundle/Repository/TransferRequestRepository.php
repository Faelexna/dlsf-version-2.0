<?php

namespace Cis\DlsfBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TransferRequestRepository extends EntityRepository
{
    public function findByAwaitingFinanceApproval($academicYear)
    {
        return $this
            ->createQueryBuilder('t')
            ->select('t')
            ->where('t.reference is null')
            ->andWhere('t.academicYear = :ayear')
            ->setParameter('ayear', $academicYear)
            ->getQuery()
            ->getResult()
        ;
    }
}
