<?php

namespace Cis\DlsfBundle\CommandBus\Admin;

use Petroc\Component\CommandBus\HandlerInterface;
use Cis\FinanceBundle\Provider\DatabaseProvider;
use Petroc\Component\Helper\Orm;

class RaisePaymentRequestHandler implements HandlerInterface
{
    private $orm;
    private $provider;

    public function __construct(Orm $orm, DatabaseProvider $provider)
    {
        $this->orm = $orm;
        $this->provider = $provider;
    }

    public function handle(RaisePaymentRequestCommand $command)
    {
        
    }
}
