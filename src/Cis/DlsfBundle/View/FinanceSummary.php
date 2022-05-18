<?php

namespace Cis\DlsfBundle\View;

use Cis\DlsfBundle\Entity\TransferRequest;
use Petroc\Component\Helper\Orm;

class FinanceSummary
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

    public function getNumPaymentAwaitingApproval()
    {
        return count($this
            ->orm
            ->getRepository(PaymentRequest::class)
            ->findByAwaitingFinanceApproval($this->academicYear)
        );
    }

    public function getNumTransferAwaitingApproval()
    {
        return count($this
            ->orm
            ->getRepository(TransferRequest::class)
            ->findByAwaitingFinanceApproval($this->academicYear)
        );
    }
}
