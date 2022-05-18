<?php

namespace Cis\FinanceBundle\Model;

use DateTime;
use InvalidArgumentException;

class PaymentDetail
{
    const MAX_LENGTH_DESCRIPTION = 500;
    const MAX_LENGTH_COST_CENTRE = 18;
    const MIN_LENGTH_COST_CENTRE = 9;

    protected $payment;
    protected $amount;
    protected $description;
    protected $nominalCode;
    protected $bankNominalCode;
    protected $refunds = [];

    public function __construct(Payment $payment, $nominalCode, $description, $amount, $bankNominalCode)
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException('Amount must be numeric.');
        }
        
        if ($amount < 0 ) {
            throw new InvalidArgumentException('Amount cannot be below 0.');
        }  

        $this->description = $description;
        $this->nominalCode = $nominalCode;
        $this->$bankNominalCode = $bankNominalCode;
        $this->amount = $amount;
        $this->payment = $payment;
        $payment->addDetail($this);       
    }
    public function getPayment()
    {
        return $this->payment;
    }
    
    public function getReference()
    {
        return $this->payment->getReference();
    }
    
    public function getNominalCode()
    {
        return $this->nominalCode;
    }
    
    public function getBankNominalcode()
    {
        return $this->bankNominalCode;
    }

    public function setBankNominalCode($nominalCode)
    {
        $this->bankNominalCode = $nominalCode;
        return $this;
    }
    
    public function setNominalCode($nominalCode)
    {
        $this->nominalCode = $nominalCode;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getRefunds()
    {
        return $this->refunds;
    }

    public function setDate(DateTime $date)
    {
        $this->date = $date;
        return $this;
    }
    
    public function getAmount()
    {
        return $this->amount;
    }
    
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function addRefund($refund)
    {
        $this->refunds[] = $refund;
        return $this;
    }
}
