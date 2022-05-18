<?php

namespace Cis\DlsfBundle\CommandBus\Admin;

use App\Repository\Dlsf\ClaimCriteria;
use App\Entity\Dlsf\Claim;
use Petroc\Component\Helper\Orm;
use Petroc\Component\CommandBus\FilterCommandInterface;
use Petroc\Component\CommandBus\FilterCommandTrait;

class FilterCommand extends ClaimCriteria implements FilterCommandInterface
{
    use FilterCommandTrait;

    const PRE_FILTER_PAYMENT = 'payment';
    const PRE_FILTER_TRANSFER = 'transfer';

    public function __construct($academicYear, $preFilter = null)
    {
        parent::__construct($academicYear);
        if (null === $preFilter) {
            return;
        }

        switch($preFilter) {
            case self::PRE_FILTER_PAYMENT:
                $this->paymentType = Claim::PAYMENT_TYPE_BACS;
                break;
            case self::PRE_FILTER_TRANSFER:
                $this->paymentType = Claim::PAYMENT_TYPE_BUDGET_TRANSFER;
                break;
        }
            
    }

    public function handle(Orm $orm)
    {
        return $this->setResult(
            $orm->getRepository(Claim::class)->match($this)
        );
    }
}
