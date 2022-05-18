<?php

namespace Cis\DlsfBundle\CommandBus\Finance;

use Cis\FinanceBundle\Provider\DatabaseProvider;
use Petroc\Component\CommandBus\HandlerInterface;

class ApproveTransferRequestHandler implements HandlerInterface
{
    private $provider;

    public function __construct(DatabaseProvider $provider)
    {
        $this->provider = $provider;
    }

    public function handle(ApproveTransferRequestCommand $command)
    {
        $transferRequest = $command->getTransferRequest();
        $reference = $this->provider->getCustomSequenceNumber($transferRequest::BATCH_REFERENCE_PREFIX);

        $transferRequest->approveTransfer($command->getUser(), $reference);
        $this->provider->addNominalTransfer($transferRequest);
    }
}
