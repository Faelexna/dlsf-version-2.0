<?php

namespace App\Repository\Dlsf;

use Doctrine\ORM\EntityRepository;
use App\Entity\Dlsf\Applicant;

class ClaimRepository extends EntityRepository
{
    public function findByPaymentTypeAwaitingProcessing(array $options)
    {
        $config = $this->getEntityManager()->getConfiguration();
        $config->addCustomNumericFunction('isnull', 'App\Doctrine\ORM\Query\IsNull');

        $claims = $this
            ->createQueryBuilder('cl')
            ->select('cl')
            ->join('cl.applicant','a')
            ->where('isnull(cl.approvedAmount,0) > isnull(cl.paidAmount,0)')
            ->andWhere('cl.approved = 1 or cl.approvedAmount > 0')
            ->andWhere('cl.paymentType = :paymentType')
            ->andWhere('a.academicYear = :ayear')
            ->setParameter('paymentType', $options['paymentType'])
            ->setParameter('ayear', $options['academicYear'])
        ;

        if (isset($options['site'])) {
            $claims
                ->andWhere('a.site = :site')
                ->setParameter('site', $options['site'])
            ;
        }

        if (isset($options['category'])) {
            $claims
                ->andWhere('cl.category = :category')
                ->setParameter('category', $options['category'])
            ;
        }

        if (isset($options['ageCategory'])) {
            $claims
                ->andWhere('a.ageCategory = :ageCategory')
                ->setParameter('ageCategory', $options['ageCategory'])
            ;
        }

        return $claims
            ->getQuery()
            ->getResult()
        ;
    }

    public function match(ClaimCriteria $criteria)
    {
        $config = $this->getEntityManager()->getConfiguration();
        $config->addCustomNumericFunction('isnull', 'App\Doctrine\ORM\Query\IsNull');

        $qb = $this
            ->createQueryBuilder('cl')
            ->addSelect('cl')
            ->addSelect('a')
            ->join('cl.applicant','a')
            ->join('cl.category', 'c')
            ->where('isnull(cl.approvedAmount,0) > isnull(cl.paidAmount,0)')
            ->andWhere('cl.approved = 1 or cl.approvedAmount > 0')
            ->andWhere('cl.paymentType = :paymentType')
            ->andWhere('a.academicYear = :ayear')
            ->setParameter('paymentType', $criteria->paymentType)
            ->setParameter('ayear', $criteria->getAcademicYear())
        ;

        if (null !== $ageCategory = $criteria->ageCategory) {
            $qb
                ->andWhere('a.ageCategory = :age_category')
                ->setParameter('age_category', $ageCategory)
            ;
        }

        if (null !== $fundingType = $criteria->fundingType) {
			if ($fundingType === Applicant::ADVANCED_LEARNING_LOAN) {
				$qb
					->andWhere('a.twentyFourPlusBursary = 1')
				;
			} elseif ($fundingType === Applicant::FE) {
				$qb
					->andWhere('a.twentyFourPlusBursary = 0')
				;
			}
		}

		if (null !== $site = $criteria->site) {
			$qb
				->andWhere('a.site = :site')
				->setParameter('site', $site)
			;
		}
    
        if (null !== $category = $criteria->category) {
			$qb
				->andWhere('cl.category = :category')
				->setParameter('category', $category)
			;
		}
        return $qb->getQuery()->getResult();
    }
}
