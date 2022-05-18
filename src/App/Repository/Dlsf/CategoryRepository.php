<?php

namespace App\Repository\Dlsf;

use Doctrine\ORM\EntityRepository;
use App\Entity\Dlsf\Claim;
use App\Entity\Dlsf\Category;


class CategoryRepository extends EntityRepository
{
    public function findByAcademicYear($academicYear)
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.academic_year = :academic_year')
            ->setParameter('academic_year', $academicYear)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findClaims(Category $category)
    {
        return $this
            ->createQueryBuilder('c')
            ->select('cl')
            ->from(Claim::class, 'cl')
            ->where('cl.category = :id')
            ->andWhere('cl.deletedOn is null')
            ->setParameter('id', $category->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function findClaimsAwaitingPayment(Category $category)
    {
        return $this
            ->createQueryBuilder('C')
            ->select('cl')
            ->from(Claim::class, 'cl')
        ;
    }
}
