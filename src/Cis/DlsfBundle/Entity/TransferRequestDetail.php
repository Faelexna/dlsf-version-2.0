<?php

namespace Cis\DlsfBundle\Entity;

use DateTime;
use App\Entity\Dlsf\Claim;
use Cis\FinanceBundle\Model\TransferDetail;

class TransferRequestDetail extends TransferDetail
{
    private $id;
    private $createdOn;
    private $claim;

    public function __construct(TransferRequest $transfer, $costCentre, $contraCode, $description, $amount, Claim $claim)
    {
        parent::__construct($transfer, $costCentre, $contraCode, $description, $amount);
        $this->createdOn = new DateTime();
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

    public function getClaim()
    {
        return $this->claim;
    }

    public function getCostCentre()
    {
        return $this->getNominalCode();
    }

    public function setClaim(Claim $claim)
    {
        $this->claim = $claim;
        return $this;
    }
}
