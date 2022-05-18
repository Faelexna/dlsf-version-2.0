<?php

namespace Cis\FinanceBundle\Model;

use DateTime;

class Transfer
{
    const MAX_LENGTH_DESCRIPTION = 500;

    protected $date;
    protected $reference;
    protected $batchReference;
    protected $description;
    protected $details = [];

    public function __construct($description, $batchReference, $reference, DateTime $date)
    {
        $this->description = $description;
        $this->batchReference = $batchReference;
        $this->reference = $reference;
        $this->date = $date;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getBatchReference()
    {
        return $this->batchReference;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDate(DateTime $date)
    {
        $this->date = $date;
        return $this;
    }

    public function setBatchReference($batchReference)
    {
        $this->batchReference = $batchReference;
        return $this;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function addDetail(TransferDetail $detail)
    {
        $this->details[] = $detail;
        return $this;
    }
}