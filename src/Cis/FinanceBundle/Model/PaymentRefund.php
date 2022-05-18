<?php

namespace Cis\FinanceBundle\Model;

use DateTime;

class PaymentRefund
{
    protected $paymentDetail;
    protected $amount;
    protected $date;
    protected $reference;

    public function __construct(PaymentDetail $paymentDetail, DateTime $date, $amount, $reference)
    {
        $this->payment = $paymentDetail;
        $this->date = $date;
        $this->amount = $amount;
        $this->reference = $reference;
        $paymentDetail->addRefund($this);
    }

    public function getPayment()
    {
        return $this->payment;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getDate()
    {
        return $this->date;
    }
    
    public function getReference()
    {
        return $this->reference;
    }

    function setDate($date)
    {
        $this->date = $date;
        return $this;
    }
    
    function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    function setPayment(Payment $payment)
    {
        $this->payment = $payment;
        return $this;
    }
}
