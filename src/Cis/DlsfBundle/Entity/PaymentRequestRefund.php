<?php

namespace Cis\DlsfBundle\Entity;

use DateTime;
use Cis\FinanceBundle\Model\PaymentRefund;
use App\Entity\User;

class PaymentRequestRefund extends PaymentRefund
{
    private $id;
    private $createdOn;
    private $addedByUser;

    public function __construct(PaymentRequest $payment, $date, $amount, $reference, User $user)
    {
        parent::__construct($payment, $date, $amount, $reference);
        $this->createdOn = new DateTime();
        $this->addedByUser = $user;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    public function getAddedByUser()
    {
        return $this->addedByUser;
    }
}
