<?php

namespace Cis\DlsfBundle\CommandBus\Finance;

use Petroc\Component\CommandBus\Command;

class ApproveTransferRequestCommand extends Command
{
    private $transferRequest;
    private $user;

    public function getTransferRequest()
    {
        return $this->transferRequest;
    }

    public function getUser()
    {
        return $this->user;
    }
}
