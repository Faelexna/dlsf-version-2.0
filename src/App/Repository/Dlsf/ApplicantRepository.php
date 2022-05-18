<?php

namespace App\Repository\Dlsf;

use App\Entity\Dlsf\Applicant;
use App\Entity\Dlsf\Claim;
use App\Entity\Dlsf\Category;
use App\Entity\Student\Student;
use App\Entity\Student\ReferenceNumber;
use Cis\DataBundle\Entity\LearnerSummary;
use Cis\DataBundle\Entity\Person;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository;

class ApplicantRepository extends EntityRepository
{
    public function isEnhancedBursaryStudent(Student $student, $year)
    {
        $applications = $this
            ->createQueryBuilder('a')
            ->select('a')
            ->join('a.student', 's')
            ->join('a.claims', 'cl')
            ->join('cl.category', 'c')
            ->where('a.academicYear = :year')
            ->andWhere('c.academicYear = :year')
            ->setParameter('year', $year)
            ->andWhere('s = :student')
            ->setParameter('student', $student)
            ->andWhere('a.evidenceSeen = 1')
            ->andWhere("c.name like '%Enhanced%'")
            ->getQuery()
            ->getResult()
        ;

        return count($applications) > 0;
    }
    
    public function findByBankDetailsRequired(Student $student, $year)
    {
        return $this
            ->createQueryBuilder('a')
            ->select('a')
            ->join('a.student', 's')
            ->join('a.claims', 'cl')
            ->join('cl.category', 'c')
            ->where('a.academicYear = :year')
            ->andWhere('c.academicYear = :year')
            ->setParameter('year', $year)
            ->andWhere('s = :student')
            ->andWhere('a.bankSortCode is null or a.bankSortCode = \'\'') 
            ->andWhere('(cl.paymentType = \'Cheque\' or c.name like \'%Free College Meal%\' or c.name like \'%Lunch%\')')
            ->setParameter('student', $student)
            ->getQuery()
            ->getResult()
        ;
    }
	public function match(ApplicantCriteria $criteria)
	{
		$config = $this->getEntityManager()->getConfiguration();
        $config->addCustomNumericFunction('isnull', 'App\Doctrine\ORM\Query\IsNull');
		
		$qb = $this
			->createQueryBuilder('a')
			->addSelect('a')
			->addSelect('s')
			->join('a.student', 's')
			->join('a.claims', 'cl')
			->join('cl.category', 'c')
			->andWhere('a.deletedOn is null')
			->andWhere('c.deletedOn is null')
		;
		
		if (null !== $academicYear = $criteria->academicYear) {
			$qb
				->andWhere('a.academicYear = :academic_year')
				->setParameter('academic_year', $academicYear)
			;
		}
		
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
		
		if (null !== $student = $criteria->student) {
			$qb
				->andWhere('s.surname like :student or s.firstName like :student or CONCAT(s.firstName, \' \', s.surname) like :student or s.idNumber like :student')
				->setParameter('student', strtolower('%' . str_replace(' ', '%', $student) . '%'))
			;
		}
		
		if (null !== $category = $criteria->category) {
			$qb
				->andWhere('cl.category = :category')
				->setParameter('category', $category)
			;
		}
		
		if (null !== $evidenceSeen = $criteria->evidenceSeen) {
			if ($evidenceSeen == 'Y') {
				$qb
					->andWhere('a.evidenceSeen = 1')
				;
			} elseif ($evidenceSeen == 'N') {
				$qb
					->andWhere('a.evidenceSeen = 0')
				;
			}
		}
		
		if (null !== $lowIncomeEvidence = $criteria->lowIncomeEvidence) {
			$qb
				->andWhere('a.lowIncomeEvidence = :low_income_evidence')
				->setParameter('low_income_evidence', $lowIncomeEvidence)
			;
		}		
		
		if (null !== $approved = $criteria->approved) {
			if ($approved == 'Y') {
				$qb
					->andWhere('cl.approved = 1')
				;
			} elseif ($approved == 'N') {
				$qb
					->andWhere('cl.approved = 0')
				;
			}
		}
		
		if (true === $criteria->enhancedBursary) {
			$qb
				->andWhere('a.incomeSupport = 1 or a.inCare = 1 or a.careLeaver = 1 or a.esaOrDsa = 1 or a.liveIndependently = 1')
			;				
		}
		
		if (true === $criteria->excludeHigherIncome) {
			$qb
				->andWhere('case when a.evidenceType = \'Low Income\' and isnull(a.householdIncome,0) < 25000 then 1 else 0 end = 0')
			;
		}
		
		if (null !== $householdIncomeOne = $criteria->householdIncomeOne or null !== $householdIncomeTwo = $criteria->householdIncomeTwo) {
			if ($householdIncomeOne !== null and $householdIncomeTwo === null) {
				$qb
					->andWhere('isnull(a.householdIncome,0) = :household_income_one')
					->setParameter('household_income_one', $householdIncomeOne)
				;
			} elseif ($householdIncomeOne !== null and $householdIncomeTwo !== null) {
				$qb
					->andWhere('isnull(a.householdIncome,0) between :household_income_one and :household_income_two')
					->setParameter('household_income_one', $householdIncomeOne)
					->setParameter('household_income_two', $householdIncomeTwo)
				;
			} elseif ($householdIncomeOne === null and $householdIncomeTwo !== null) {
				$qb
					->andWhere('isnull(a.householdIncome,0) < :household_income_two')
					->setParameter('household_income_two', $householdIncomeTwo)
				;
			}
			$qb
				->andWhere('case when a.evidenceSeen = 0 and a.householdIncome is null then 0 else 1 end = 1');
		}
		
		if (null !== $orderBy = $criteria->orderBy) {
			if ($orderBy === 'evidence_seen') {
				$qb
					->orderBy('a.evidenceSeenDate', 'DESC')
				;
			} elseif ($orderBy === 'timestamp') {
				$qb
					->orderBy('a.createdOn', 'DESC')
				;
			} elseif ($orderBy === 'name') {
				$qb
					->orderBy('s.surname', 'ASC')
					->addOrderBy('s.firstName', 'ASC')
				;
			}	
		}
		return $qb->getQuery()->getResult();
	}
}