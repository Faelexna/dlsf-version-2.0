<?php

namespace Cis\DlsfBundle\View;

use App\Entity\Dlsf\Category;
use App\Entity\Dlsf\EvidenceType;
use App\Entity\Dlsf\Claim;
use App\Entity\Misc\GeneralCode;
use App\Repository\Dlsf\ClaimRepository;
use Petroc\Component\Helper\Orm;

class AdminSummary
{
    private $orm;
    private $academicYear;

    public function __construct(Orm $orm, int $academicYear)
    {
        $this->orm = $orm;
        $this->academicYear = $academicYear;
    }

    public function getAcademicYear()
    {
        return $this->academicYear;
    }

    public function getCategories()
    {
        $categories = $this
            ->orm
            ->getRepository(Category::class)
            ->findBy([
                'academicYear' => $this->academicYear])
        ;
        return $categories;
    }

    public function getEvidenceTypes()
    {
        $evidenceTypes = $this
            ->orm
            ->getRepository(EvidenceType::class)
            ->findBy([
                'academicYear' => $this->academicYear,
                'type' => EvidenceType::EVIDENCE_TYPE_CODE
            ], ['ordering' => 'ASC'])
        ;
        return $evidenceTypes;
    }

    public function getBenefitsTypes()
    {
        $benefitTypes = $this
            ->orm
            ->getRepository(GeneralCode::class)
            ->findBy([
                'academicYear' => $this->academicYear, 
                'category' => GeneralCode::CATEGORY_STUDENT_DLSF_BENEFIT_TYPE,
                'archived' => 0
            ], ['groupLabel' => 'ASC'])
        ;
        return $benefitTypes;
    }

    public function getNumAwaitingPayment()
    {
        return count($this
            ->orm
            ->getRepository(Claim::class)
            ->findByPaymentTypeAwaitingProcessing([
                'paymentType' => Claim::PAYMENT_TYPE_BACS, 
                'academicYear' =>$this->academicYear
           ])
        );
    }

    public function getNumAwaitingTransfer()
    {
        return count($this
            ->orm
            ->getRepository(Claim::class)
            ->findByPaymentTypeAwaitingProcessing([
                'paymentType' => Claim::PAYMENT_TYPE_BUDGET_TRANSFER,
                'academicYear' =>$this->academicYear
            ])
        );
    }
}