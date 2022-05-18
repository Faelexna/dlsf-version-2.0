<?php

namespace Cis\FinanceBundle\Model;

use DateTime;
use InvalidArgumentException;

class TransferDetail
{
    const MAX_LENGTH_DESCRIPTION = 500;
    const MAX_LENGTH_COST_CENTRE = 18;
    const MIN_LENGTH_COST_CENTRE = 9;

    protected $transfer;
    protected $amount;
    protected $description;
    protected $nominalCode;
    protected $contraCode;

    public function __construct(Transfer $transfer, $nominalCode, $contraCode, $description, $amount)
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException('Amount must be numeric.');
        } 

        $this->description = $description;
        $this->nominalCode = $nominalCode;
        $this->contraCode = $contraCode;
        $this->amount = $amount;
        $this->transfer = $transfer;
        $transfer->addDetail($this);       
    }
    public function getTransfer()
    {
        return $this->transfer;
    }
    
    public function getReference()
    {
        return $this->transfer->getReference();
    }
    
    public function getNominalCode()
    {
        return $this->nominalCode;
    }
    
    public function setNominalCode($nominalCode)
    {
        $this->nominalCode = $nominalCode;
        return $this;
    }

    public function getContraCode()
    {
        return $this->contraCode;
    }

    public function setContraCode($contraCode)
    {
        $this->contraCode = $contraCode;
    }

    public function getDescription()
    {
        return $this->description;
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
}
