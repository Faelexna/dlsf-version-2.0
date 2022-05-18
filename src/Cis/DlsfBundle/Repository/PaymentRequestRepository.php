<?php

namespace Cis\DlsfBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PaymentRequestRepository extends EntityRepository
{
    public function findByAwaitingFinanceApproval($academicYear)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.reference is null')
            ->andWhere('p.academicYear = :ayear')
            ->setParameter('ayear', $academicYear)
            ->getQuery()
            ->getResult()
        ;
    }
}
