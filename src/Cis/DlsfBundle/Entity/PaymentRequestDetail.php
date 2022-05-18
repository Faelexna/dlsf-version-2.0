<?php

namespace Cis\DlsfBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Cis\FinanceBundle\Model\PaymentDetail;
use App\Entity\Dlsf\Claim;

class PaymentRequestDetail extends PaymentDetail
{
    const BANK_COST_CENTRE = 'ZN03-9515';

    private $id;
    private $createdOn;
    private $claim;

    public function __construct(PaymentRequest $payment, $costCentre, $description, $amount, Claim $claim)
    {
        parent::__construct($payment, $costCentre, $description, $amount, self::BANK_COST_CENTRE);
        $this->createdOn = new DateTime();
        $this->refunds = new ArrayCollection();
        $this->claim = $claim;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    public function getCostCentre()
    {
        return $this->getNominalCode();
    }

    public function getClaim()
    {
        return $this->claim;
    } 

    public function setClaim(Claim $claim)
    {
        $this->claim = $claim;
        return $this;
    }
}
