<?php

namespace Cis\DlsfBundle\CommandBus\Finance;

use Cis\FinanceBundle\Provider\DatabaseProvider;
use Petroc\Component\CommandBus\HandlerInterface;

class ApprovePaymentRequestHandler implements HandlerInterface
{
    private $provider;
    
    public function __construct(DatabaseProvider $provider)
    {
        $this->provider = $provider;
    }

    public function handle(ApprovePaymentRequestCommand $command)
    {
        $paymentRequest = $command->getPaymentRequest();
        $reference = $this->provider->getCustomSequenceNumber($paymentRequest::BATCH_REFERENCE_PREFIX);

        $paymentRequest->approvePayment($command->getUser(), $reference);
        $this->provider->addNominalPayment($paymentRequest);
    }
}
