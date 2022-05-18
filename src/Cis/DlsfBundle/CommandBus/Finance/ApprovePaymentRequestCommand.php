<?php

namespace Cis\DlsfBundle\CommandBus\Finance;

use Petroc\Component\CommandBus\Command;

class ApprovePaymentRequestCommand extends Command
{
    private $paymentRequest;
    private $user;

    public function getPaymentRequest()
    {
        return $this->paymentRequest;
    }

    public function getUser()
    {
        return $this->user;
    }
}
