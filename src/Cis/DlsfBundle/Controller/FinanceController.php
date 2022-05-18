<?php

namespace Cis\DlsfBundle\Controller;

use Cis\DlsfBundle\Entity\PaymentRequest;
use Cis\DlsfBundle\Entity\TransferRequest;
use Cis\DlsfBundle\View\FinanceSummary;
use Cis\DlsfBundle\Form\AddIncomeType;
use Cis\DlsfBundle\CommandBus\Finance as Command;

class FinanceController extends Controller
{
	public function indexAction($academicYear)
    {
		return $this
			->createView()
			->setData(new FinanceSummary($this->orm,$academicYear), 'summary')
			//->restrictTo(Self::FINANCE_ACCESS_RULE)
		;
    }

    public function addIncomeAction($academicYear)
    {
        $command = new Command\AddIncomeCommand($academicYear);

        return $this
            ->createFormview(AddIncomeType::class, $command)
            //->restrictTo(Self::FINANCE_ACCESS_RULE)
            ->setTemplateDate(['academicYear' => $academicYear])
            ->onSuccessRoute(Self::FINANCE_INDEX_ROUTE)
        ;
    }
    
    public function viewTransferRequestsAction($academicYear)
    {
        $transfers = $this
            ->orm
            ->getRepository(TransferRequest::class)
            ->findByAwaitingFinanceApproval($academicYear)
        ;

        return $this
            ->createView()
            ->setData($transfers, 'transfers')
            //->retrictTo(Self::FINANCE_ACCESS_RULE)
        ;
    }

    public function viewPaymentRequestsAction($academicYear)
    {
        $payments = $this
            ->orm
            ->getRepository(PaymentRequest::class)
            ->findByAwaitingFinanceApproval($academicYear)
        ;

        return $this
            ->createView()
            ->setData($payments, 'payments')
            //->restrictTo(Self::FINANCE_ACCESS_RULE)
        ;

    }

    public function viewPaymentRequestAction(PaymentRequest $payment)
    {
        return $this
            ->createView()
            ->setData($payment, 'payment')
            //->restrictTo(Self::FINANCE_ACCESS_RULE)
        ;
    }

    public function viewTransferRequestAction(TransferRequest $transfer)
    {
        return $this
            ->createView()
            ->setData($transfer, 'transfer')
            //->restrictTo(Self::FINANCE_ACCESS_RULE)
        ;
    }

    public function approveTransferRequestAction(TransferRequest $transfer)
    {
        $command = new Command\ApproveTransferRequestCommand($transfer, $this->security->getUser());

        return $this
            ->createConfirmationView($command)
            //->restrictTo(Self::FINANCE_ACCESS_RULE)
            ->onSuccessRoute(Self::FINANCE_INDEX_ROUTE)
        ;
    }

    public function approvePaymentRequestAction(PaymentRequest $payment)
    {
        $command = new Command\ApprovePaymentRequestCommand($payment, $this->security->getUser());

        return $this
            ->createConfirmationView($command)
            //->restrictTo(Self::FINANCE_ACCESS_RULE)
            ->onSuccessRoute(Self::FINANCE_INDEX_ROUTE)
        ;
    }
}



