<?php

namespace Cis\FinanceBundle\Provider;

use Cis\FinanceBundle\Model\Customer;
use Cis\FinanceBundle\Model\Invoice;
use Cis\FinanceBundle\Model\InvoiceCredit;
use Cis\FinanceBundle\Model\InvoiceRefund;
use Cis\FinanceBundle\Model\TaxCodes;
use Cis\FinanceBundle\Model\Payment;
use Cis\FinanceBundle\Model\PaymentRefund;
use Cis\FinanceBundle\Model\Transfer;
use Cis\FinanceBundle\Model\Income;
use Doctrine\DBAL\Connection;
use Exception;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;

class DatabaseProvider implements ProviderInterface
{
    const DATE_FORMAT = 'Y-m-d';

    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    private function createTransactionId($sequence)
    {
        $sql = sprintf('DECLARE @TRAN_ID INT
                EXEC AA_GET_SEQUENCE_NO_S "%s", 1, @TRAN_ID OUT
                SELECT @TRAN_ID',$sequence);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        if (false === $stmt->fetchColumn() or is_null($stmt->fetchColumn())) {
            $sql = 'select CTL_SEQ_NUMBER from dbo.SYS_SEQCONTRL where ctl_seq_name like \''.$sequence.'\'';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        }
        return $stmt->fetchColumn();
    }

    private function getAccountPrimaryKey($customerCode)
    {
        $sql = "SELECT CU_PRIMARY FROM SL_ACCOUNTS WHERE CUCODE = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, (string) $customerCode);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function addOrUpdateCustomer(Customer $customer)
    {
        $address1 = $customer->getAddress1();

        if(!empty($customer->getAddress2())) {
            $address1 .= PHP_EOL . $customer->getAddress2();
        }

        $insertFlag = 1;
        if($primary = $this->getAccountPrimaryKey($customer->getReference())) {
            $insertFlag = 0;
        }

        $sql = 'DECLARE @PROCEDURE_ERROR INT; EXEC @PROCEDURE_ERROR = AA_SL_ACCOUNT_INSERT_EDIT_S';
        $sql .=  '  @PS_Insert_Flag=?';
        $sql .=  ', @PS_User_Id=?';
        $sql .=  ', @PS_Account=?';
        $sql .=  ', @PS_Account_Type=?';
        $sql .=  ', @PS_Name=?';
        $sql .=  ', @PS_Address=?';
        $sql .=  ', @PS_Address_User1=?';
        $sql .=  ', @PS_Address_User2=?';
        $sql .=  ', @PS_Postcode=?';
        $sql .=  ', @PS_Phone=?';
        $sql .=  ', @PS_Fax=?';
        $sql .=  ', @PS_Email=?';
        $sql .=  ', @PS_Sort=?';
        $sql .=  ', @PS_User1=?';
        if ($primary) {
            $sql .=  ', @PS_Account_Primary=?;';
        }
        $sql .=  ' SELECT @PROCEDURE_ERROR;';

        $params = [
            $insertFlag,
            'WAPI',
            $customer->getReference(),
            'C',
            $customer->getName(),
            $address1,
            $customer->getTown(),
            $customer->getCounty(),
            $customer->getPostcode(),
            $customer->getPhone(),
            $customer->getMobile(),
            $customer->getEmail(),
            $customer->getType(),
            'COURSE'
        ];

        if ($primary) {
            $params[] = $primary;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        if (0 !== $error = $stmt->fetchColumn()) {
            throw new Exception(sprintf('Error creating/updating customer: %s', $error));
        }
    }

    private function processTransaction($transactionId)
    {
        $sql = sprintf('DECLARE @HEADER_PRIMARY INT, @PROCEDURE_ERROR INT
            EXEC @PROCEDURE_ERROR = AA_PROCESS_SL_TRN_TEMP_S %s, "WAPI", 2, @HEADER_PRIMARY OUT
            SELECT @PROCEDURE_ERROR',
            $transactionId
        );

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        /*
        if ($throwError and 0 !== $error = $stmt->fetchColumn()) {
            throw new Exception(sprintf('Error creating invoice: %s', $error));
        }*/
    }

    private function processNominalTransaction($transactionId)
    {
        $sql = sprintf('DECLARE @HEADER_PRIMARY INT, @PROCEDURE_ERROR INT
            EXEC @PROCEDURE_ERROR = AA_PROCESS_NL_TRN_TEMP_S %s, "WAPI", 2, @HEADER_PRIMARY OUT
            SELECT @PROCEDURE_ERROR',
            $transactionId
        );

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }

    private function processCreditRefundNote($transactionId)
    {
        $sql = sprintf('DECLARE @PROCEDURE_ERROR INT
            EXEC @PROCEDURE_ERROR = AA_TRN_MULTI_ALLOCATE_S  0, "S", %s,\'\',0,FALSE,0,NULL,NULL,NULL,"WAPI"
            SELECT @PROCEDURE_ERROR',
            $transactionId
        );

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        #if (null !== $error = $stmt->fetchColumn()) {
        #    throw new Exception(sprintf('Error creating credit / refund: %s', $error));
        #}

    }

    private function getPrimary($reference,$transactionType) {
        $sql = 'SELECT ST_PRIMARY FROM SL_TRANSACTIONS WHERE ST_HEADER_REF = '.$this->conn->quote($reference).' and st_trantype = \''.$transactionType.'\'';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function fixEncoding($value)
    {
        return str_replace('Â£', chr(163), $value);
    }

    public function addInvoice(Invoice $invoice)
    {
        $id = $this->createTransactionId('SL_TRAN_ID');
        $dates = [];
        $dueDates = $invoice->getDueDates();

        $conn = $this->conn;

        foreach ($dueDates as $dueDate) {
            $dates[] = $dueDate->getDate();
        }

        $numDueDates = count($dueDates);

        $minDueDate = null;
        if ($numDueDates > 0) {
            $minDueDate = $conn->quote(min($dates)->format(self::DATE_FORMAT));
        }

        $values = [
            'sl_do_not_batch' => 0,
            'sl_user_id' => $conn->quote('WAPI'),
            'sl_status_temp' => 0,
            'sl_customer_code' => $conn->quote($invoice->getCustomer()->getReference()),
            'sl_tran_reference' => $conn->quote($invoice->getReference()),
            'sl_batch_reference' => $conn->quote($invoice->getBatchReference()),
            'sl_tran_id' => $id,
            'sl_note' => $this->fixEncoding($conn->quote($invoice->getDescription())),
            'sl_use_multiple_due_dates' => ($numDueDates > 0) ? 1 : 0,
            'sl_multiple_due_date_start_date' => $minDueDate,
            'sl_multiple_due_date_payment_no' => $numDueDates,
            'sl_multiple_due_dates_xml' => $conn->quote($this->generateDueDatesXml($dueDates)),
            'sl_detail_home_value' => null,
            'sl_header_user_flag1' => !$invoice->isMoreToFollow() ? 1 : 0,
            'sl_detail_description' => null,
            'sl_detail_vatcode' => null,
            'sl_tran_description' => $conn->quote($invoice->getHeading()),
            'sl_detail_analysis_code' => null,
            'sl_detail_line_no' => null,
            'sl_detail_user_char3' => null
        ];

        $columns = array_keys($values);

        $lineNumber = 1;
        foreach ($invoice->getDetails() as $detail) {
            $values['sl_detail_home_value'] = $detail->getAmount();
            $values['sl_detail_description'] = $conn->quote($detail->getDescription());
            $values['sl_detail_vatcode'] = $conn->quote($detail->getTaxCode());
            $values['sl_detail_analysis_code'] = $conn->quote($detail->getNominalCode());
            $values['sl_detail_user_char3'] = $this->fixEncoding($conn->quote($detail->getHeading()));
            $values['sl_detail_line_no'] = $lineNumber;
            $lineNumber++;

            $values = array_map(function($value) {
                return null === $value ? 'null' : $value;
            }, $values);

            $sql = sprintf(
                'insert into sl_trn_temp (%s) values (%s)',
                implode($columns, ','),
                implode($values, ',')
            );
            $this->conn->executeQuery($sql);
        }

        return $this->processTransaction($id);
    }

    private function generateDueDatesXml($dueDates)
    {
       $xml = '<LIST>';
        foreach ($dueDates as $dueDate) {
            $xml .= sprintf(
                '<LINE DUE_DATE="%s" AMOUNT="%s" />',
                $dueDate->getDate()->format(self::DATE_FORMAT),
                $dueDate->getAmount()
            );
        }
        return $xml . '</LIST>';
    }

    private function getAgreementLearners($agreement)
    {
        $learners = '';
        foreach ($agreement->getLearners() as $learner) {
            $learners .= $learner->getStudent()->getIdNumber().' - '.$learner->getStudent()->getFullName().chr(10).chr(13);
        }
        return $learners;
    }

    public function processRefundCredit(InvoiceCredit $credit = null, $refund = null)
    {
        $processCredit = [];
        $creditId = $this->createTransactionId('SL_TRAN_ID');
        $lineNumber = 1;
        if ($credit->getTrainingAmount() > 0) {
            $processCredit[] = ['id' => $creditId,
                                'amount' => $credit->getTrainingAmount(),
                                'type' => 'TRAINING',
                                'lineNumber' => $lineNumber,
                                'description' => 'Training'];
            $lineNumber++;
        }
        if ($credit->getAssessmentAmount() > 0) {
            $processCredit[] = ['id' => $creditId,
                                'amount' => $credit->getAssessmentAmount(),
                                'type' => 'ASSESSMENT',
                                'lineNumber' => $lineNumber,
                                'description' => 'Assessment'];
        }

        $optionsDetail = ['id' => $this->createTransactionId('SL_AL_ID'),
                        'lineNumber' => 1,
                        'reference' => $credit->getInvoice()->getReference(),
                        'transactionType' => 'INV',
                        'amount' => $credit->getRemainingAmountToPay()];
        $this->addAllocationLine($optionsDetail);
        if ($credit instanceOf InvoiceCredit) {
            foreach ($processCredit as $process) {
                $this->addCredit($credit, $process);
            }
            $this->processTransaction($creditId);
            $optionsDetail['lineNumber']++;
            $optionsDetail['transactionType'] = 'CRN';
            $optionsDetail['amount'] = $credit->getAmount();
            $this->addAllocationLine($optionsDetail);
        }

        if (is_object($refund)) {
            $this->addRefund($refund, $credit->getInvoice());
            $optionsDetail['lineNumber']++;
            $optionsDetail['reference'] = $refund->getChequeNumber();
            $optionsDetail['transactionType'] = 'ADR';
            $optionsDetail['amount'] = $refund->getAmount();
            $this->addAllocationLine($optionsDetail);
        }
        return $this->processCreditRefundNote($optionsDetail['id']);
    }

    public function processRefundAndCredit(InvoiceRefund $refund, InvoiceCredit $credit)
    {
        $processCredit = [];
        $creditId = $this->createTransactionId('SL_TRAN_ID');
        $lineNumber = 1;

        $amount = $credit->getAmount() - $refund->getAmount();

        if ($amount < 0) {
            throw new InvalidArgumentException('Amount is less than 0');
        }

        if ($credit->getTrainingAmount() > 0) {
            $processCredit[] = ['id' => $creditId,
                                'amount' => $credit->getTrainingAmount(),
                                'type' => 'TRAINING',
                                'lineNumber' => $lineNumber,
                                'description' => 'Training'];
            $lineNumber++;
        }
        if ($credit->getAssessmentAmount() > 0) {
            $processCredit[] = ['id' => $creditId,
                                'amount' => $credit->getAssessmentAmount(),
                                'type' => 'ASSESSMENT',
                                'lineNumber' => $lineNumber,
                                'description' => 'Assessment'];
        }

        $optionsDetail = ['id' => $this->createTransactionId('SL_AL_ID'),
                        'lineNumber' => 1,
                        'reference' => $credit->getInvoice()->getReference(),
                        'transactionType' => 'INV',
                        'amount' => $amount];
        $this->addAllocationLine($optionsDetail);

        foreach ($processCredit as $process) {
            $this->addCredit($credit, $process);
        }

        $this->processTransaction($creditId);
        $optionsDetail['lineNumber']++;
        $optionsDetail['reference'] = $credit->getReference();
        $optionsDetail['transactionType'] = 'CRN';
        $optionsDetail['amount'] = $credit->getAmount();
        $this->addAllocationLine($optionsDetail);

        $this->addRefund($refund, $credit->getInvoice());
        $optionsDetail['lineNumber']++;
        $optionsDetail['reference'] = $refund->getChequeNumber();
        $optionsDetail['transactionType'] = 'ADR';
        $optionsDetail['amount'] = $refund->getAmount();
        $this->addAllocationLine($optionsDetail);

        return $this->processCreditRefundNote($optionsDetail['id']);
    }

    public function processCredit(InvoiceCredit $credit)
    {

        $processCredit = [];
        $creditId = $this->createTransactionId('SL_TRAN_ID');
        $lineNumber = 1;
        if ($credit->getTrainingAmount() > 0) {
            $processCredit[] = ['id' => $creditId,
                                'amount' => $credit->getTrainingAmount(),
                                'type' => 'TRAINING',
                                'lineNumber' => $lineNumber,
                                'description' => 'Training'];
            $lineNumber++;
        }
        if ($credit->getAssessmentAmount() > 0) {
            $processCredit[] = ['id' => $creditId,
                                'amount' => $credit->getAssessmentAmount(),
                                'type' => 'ASSESSMENT',
                                'lineNumber' => $lineNumber,
                                'description' => 'Assessment'];
        }

        $optionsDetail = ['id' => $this->createTransactionId('SL_AL_ID'),
                        'lineNumber' => 1,
                        'reference' => $credit->getInvoice()->getReference(),
                        'transactionType' => 'INV',
                        'amount' => $credit->getAmount()];

        $this->addAllocationLine($optionsDetail);

        foreach ($processCredit as $process) {
            $this->addCredit($credit, $process);
        }
        $this->processTransaction($creditId);
        $optionsDetail['lineNumber']++;
        $optionsDetail['reference'] = $credit->getReference();
        $optionsDetail['transactionType'] = 'CRN';
        $optionsDetail['amount'] = $credit->getAmount();
        $this->addAllocationLine($optionsDetail);

        return $this->processCreditRefundNote($optionsDetail['id']);
    }

    private function addCredit(InvoiceCredit $credit, array $process)
    {
        $invoice = $credit->getInvoice();
        $learner = $this->getAgreementLearners($invoice->getAgreement());
        $options = ['id' => $process['id'],
                    'lineNumber' => $process['lineNumber'],
                     'doNotBatch' => 1,
                     'customerReference' => $invoice->getCustomer()->getReference(),
                     'transactionReference' => $credit->getReference(),
                     'batchReference' => $invoice->getBatchReference(),
                     'note' => $invoice->getAgreement()->getCourse().chr(10).chr(13).chr(10).chr(13).$learner,
                     'transactionType' => 'CRN',
                     'headerFlag' => !$invoice->isMoreToFollow() ? 1 : 0,
                     'description' => 'Credit Note - '.$process['description'],
                     'amount' => $process['amount']];
        foreach ($invoice->getDetails() as $detail) {
            $options['taxCode'] = $detail->getTaxCode();
            $options['nominalCode'] = $detail->getNominalCode();
            $options['heading'] = $detail->getHeading();
        }
        $this->addTransaction($options);
    }

    public function processRefund(InvoiceRefund $refund)
    {
        $invoice = $refund->getInvoice();

        $optionsDetail = ['id' => $this->createTransactionId('SL_AL_ID'),
                        'lineNumber' => 1,
                        'reference' => $invoice->getReference(),
                        'transactionType' => 'INV',
                        'amount' => $refund->getAmount()];
        $this->addAllocationLine($optionsDetail);

        $this->addRefund($refund);
        $optionsDetail['lineNumber']++;
        $optionsDetail['reference'] = $refund->getChequeNumber();
        $optionsDetail['transactionType'] = 'ADR';
        $optionsDetail['amount'] = $refund->getAmount();
        $this->addAllocationLine($optionsDetail);

        return $this->processCreditRefundNote($optionsDetail['id']);
    }

    private function addRefund(InvoiceRefund $refund)
    {
        $invoice = $refund->getInvoice();
        $id = $this->createTransactionId('SL_TRAN_ID');

        $learner = $this->getAgreementLearners($invoice->getAgreement());
        $options = ['id' => $id,
                    'lineNumber' => 1,
                     'doNotBatch' => 1,
                     'customerReference' => $invoice->getCustomer()->getReference(),
                     'transactionReference' => $refund->getChequeNumber(),
                     'batchReference' => $invoice->getBatchReference(),
                     'note' => $invoice->getAgreement()->getCourse().chr(10).chr(13).chr(10).chr(13).$learner,
                     'transactionType' => 'ADR',
                     'headerFlag' => !$invoice->isMoreToFollow() ? 1 : 0,
                     'description' => 'Refund for Invoice '.$invoice->getReference(),
                     'nominalCode' => 'ZN03-9515',
                     'amount' => $refund->getAmount()];
        foreach ($invoice->getDetails() as $detail) {
            $options['taxCode'] = $detail->getTaxCode();
            $options['heading'] = $detail->getHeading();
        }
        $this->addTransaction($options);
        $this->processTransaction($id);
    }

    private function addTransaction(array $options)
    {
        $values = [
            'sl_do_not_batch' => $options['doNotBatch'],
            'sl_user_id' => $this->conn->quote('WAPI'),
            'sl_status_temp' => 0,
            'sl_customer_code' => $this->conn->quote($options['customerReference']),
            'sl_tran_reference' => $this->conn->quote($options['transactionReference']),
            'sl_batch_reference' => $this->conn->quote($options['batchReference']),
            'sl_tran_id' => $options['id'],
            'sl_note' => $this->fixEncoding($this->conn->quote($options['note'])),
            'sl_transaction_type' => $this->conn->quote($options['transactionType']),
            'sl_detail_home_value' => $options['amount'],
            'sl_header_user_flag1' => $options['headerFlag'],
            'sl_detail_description' => $this->conn->quote($options['description']),
            'sl_detail_vatcode' => $this->conn->quote($options['taxCode']),
            'sl_detail_analysis_code' => $this->conn->quote($options['nominalCode']),
            'sl_detail_line_no' => $options['lineNumber'],
            'sl_detail_user_char3' => $this->fixEncoding($this->conn->quote($options['heading']))
        ];

        if (isset($options['transactionDescription'])) {
            $values['sl_tran_description'] = $this->conn->quote($options['transactionDescription']);
        }

        if (isset($options['transactionPeriod'])) {
            $values['sl_tran_year'] = $this->conn->quote('C');
            $values['sl_tran_period'] = $options['transactionPeriod'];
        }

        $columns = array_keys($values);
        $values = array_map(function($value) {
            return null === $value ? 'null' : $value;
        }, $values);

        $sql = sprintf('insert into sl_trn_temp (%s) values (%s)',implode($columns, ','),implode($values, ','));
        $this->conn->executeQuery($sql);
    }

    private function addNominalTransfer(Transfer $transfer)
    {
        $id = $this->createTransactionId('NL_TRAN_ID');

        $lineNumber = 1;

        $options = [
            'id' => $id,
            'transactionReference' => $transfer->getReference(),
            'batchReference' => $transfer->getBatchReference(),
            'transactionDescription' => $transfer->getDescription()
        ];

        foreach($transfer->getDetails() as $detail) {
            $contraCode = substr($detail->getContraCode(), 0, 9);
            $costHeader = '';
            if (strlen(trim($detail->getContraCode()) > 9)) {
                if (substr($detail->getContraCode(), 9 ,1) === ' ' || substr($detail->getContraCode(), 9, 1) === '-') {
                    $costHeader= substr($detail->getContraCode(), 10, 8);
                } else {
                    $costHeader = substr($detail->getContraCode(), 9, 8);
                }
            }
            if ($costHeader !== '') {
                $costCentre = substr($costHeader, 0, 4) . 'INC';
            }

            if ($detail instanceof \Cis\DlsfBundle\Entity\IncomeDetail) {
                $crDr = 'CR';
                $amount = $detail->getAmount();
            } else {
                if ($detail->getAmount() < 0 ) {
                //Double Check This is Correct
                    $crDr = 'DR';
                    $amount = $detail->getAmount() * -1;
                } else {
                    $crDr = 'CR';
                    $amount = $detail->getAmount();
                }
            }

            $options['lineNumber'] = $lineNumber;
            $options['creditDebitTransaction'] = $crDr;
            $options['nominalCode'] = $detail->getNominalCode();
            $options['amount'] = $amount;
            $options['description'] = $detail->getDescription(); 

            if ($detail instanceof \Cis\DlsfBundle\Entity\TransferRequestDetail) {
                $options['applicantObjectId'] = $detail->getClaim()->getApplicant()->getId();
                $options['claimObjectId'] = $detail->getClaim()->getId();
                $options['applicantName'] = $detail->getClaim()->getApplicant()->getStudent()->getFullName();
                $options['studentId'] = $detail->getClaim()->getApplicant()->getStudent()->getIdNumber();
            }

            if ($transfer instanceof \Cis\DlsfBundle\Entity\TransferRequest || $transfer instanceof \Cis\DlsfBundle\Entity\Income) {
                $options['journalLineReference'] = $transfer->getId();
            } 

            $this->addNominalTransaction($options);
            $lineNumber++;

            if ($crDr === 'CR') {
                $options['creditDebitTransaction'] = 'DR';
            } else {
                $options['creditDebitTransaction'] = 'CR';
            }
            $options['nominalCode'] = $contraCode;
            if ($costHeader !== '') {
                $options['projectCode'] = $costHeader;
                $options['projectAnalysis'] = $costCentre;
            }
            $this->addNominalTransaction($options);
            $lineNumber++;
        }
        $this->processNominalTransaction($id);
    }

    private function addNominalPayment(Payment $payment)
    {
        $id = $this->createTransactionId('NL_TRAN_ID');
        
        $lineNumber = 1;
        $bankTotal = 0;

        $options = [
            'id' => $id,
            'transactionReference' => $payment->getReference(),
            'batchReference' => $payment->getBatchReference(),
            'transactionDescription' => $payment->getDescription()
        ];

        foreach($payment->getDetails() as $detail) {
            $options['lineNumber'] = $lineNumber;
            $options['creditDebitTransaction'] = 'DR';
            $options['nominalCode'] = $detail->getNominalCode();
            $options['amount'] = $detail->getAmount();
            $options['description'] = $detail->getDescription();

            if ($detail instanceof \Cis\DlsfBundle\Entity\PaymentRequestDetail) {
                $options['applicantObjectId'] = $detail->getClaim()->getApplicant()->getId();
                $options['claimObjectId'] = $detail->getClaim()->getId();
                $options['applicantName'] = $detail->getClaim()->getApplicant()->getStudent()->getFullName();
                $options['studentId'] = $detail->getClaim()->getApplicant()->getStudent()->getIdNumber();
            }

            if ($payment instanceof \Cis\DlsfBundle\Entity\PaymentRequest) {
                $options['journalLineReference'] = $payment->getId();
            }

            $this->addNominalTransaction($options);
            $lineNumber++;
            $bankTotal+= $detail->getAmount();
            $bankNominalCode = $detail->getBankNominalcode();
        }

        if (isset($options['applicantObjectId'])) {
            unset($options['applicantObjectId']);
            unset($options['claimObjectId']);
            unset($options['applicantName']);
            unset($options['studentId']);
        }

        $options['lineNumber'] = $lineNumber;
        $options['creditDebitTransaction'] = 'CR';
        $options['nominalCode'] = $bankNominalCode;
        $options['amount'] = $bankTotal;
        $options['description'] = $payment->getDescription();
        $this->addNominalTransaction($options);
        $this->processNominalTransaction($id);
    }

    private function addNominalTransaction(array $options)
    {
        $values = [
            'nl_tran_id' => $options['id'],
            'nl_detail_line_no' => $options['lineNumber'],
            'nl_status_temp' => 0,
            'nl_user_id' => $this->conn->quote('WAPI'),
            'nl_module' => $this->conn->quote('NL'),
            'nl_transaction_type' => $this->conn->quote('JNL'),
            'nl_tran_reference' => $this->conn->quote($options['transactionReference']),
            'nl_batch_reference' => $this->conn->quote($options['batchReference']),
            'nl_credit_debit_transaction' => $this->conn->quote($options['creditDebitTransaction']),
            'nl_journal_account' => $this->conn->quote($options['nominalCode']),
            'nl_home_transaction_value' => $options['amount'],
            'nl_detail_description' => $this->conn->quote($options['description'])
        ];

        if (isset($options['transactionPeriod'])) {
            $values['nl_transaction_period'] = $options['transactionPeriod'];
            $values['nl_transaction_year'] = $this->conn->quote($options['transactionYear']);
        }

        if (isset($options['transactionDescription'])) {
            $values['nl_description'] = $this->conn->quote($options['transactionDescription']);
        }

        if (isset($options['projectCode'])) {
            $values['nl_costheader'] = $this->conn->quote($options['projectCode']);
            $values['nl_costcentre'] = $this->conn->quote($options['projectAnalysis']);
        }                                                                                                                   

        if (isset($options['journalLineReference'])) {
            $values['nl_detail_jnl_reference'] = $this->conn->quote($options['journalLineReference']);
        }

        if (isset($options['applicantObjectId'])) {
            $values['nl_detail_user_number1'] = $options['applicantObjectId'];
            $values['nl_detail_user_number2'] = $options['claimObjectId'];
            $values['nl_detail_user_char3'] = $this->conn->quote($options['applicantName']);
            $values['nl_detail_user_char1'] = $this->conn->quote($options['studentId']);
        }

        $columns = array_keys($values);
        $values = array_map(function($value) {
            return null === $value ? 'null' : $value;
        }, $values);

        $sql = sprintf('INSERT INTO NL_TRN_TEMP (%s) VALUES (%s)',implode($columns, ','),implode($values, ','));
        $this->conn->executeQuery($sql);
    }

    private function addAllocationLine(array $options)
    {
        $values = [
            'sl_al_id' => $options['id'],
            'sl_al_line_number' => $options['lineNumber'],
            'sl_al_status' => 0,
            'sl_al_primary' => $this->conn->quote($this->getPrimary($options['reference'],$options['transactionType'])),
            'sl_al_user_id' => $this->conn->quote('WAPI'),
            'sl_al_value' => $options['amount'],
            'sl_al_dispute' => 0
        ];
        $columns = array_keys($values);
        $sql = sprintf('insert into SL_ALLOCATION_TEMP (%s) values (%s)', implode($columns, ','), implode($values, ','));

        $this->conn->executeQuery($sql);
    }

    public function getLevyPayments()
    {
        $sql = "SELECT * FROM SL_TRANSACTIONS T WHERE ST_TRANTYPE = 'PAY' AND ST_COPYCUST = 'LEVY001S'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAgreementTillPayments()
    {
        $sql = "SELECT
                    *
                FROM
                    SL_PL_NL_DETAIL D
                    LEFT OUTER JOIN
                    SL_PL_NL_DETAIL2 D2 ON D2.DET_PRIMARY_2 = D.DET_PRIMARY
                    JOIN OPENQUERY(QRCS11, 'SELECT * FROM PETROC_APP_AGREEMENT_INVOICE') I ON I.REFERENCE COLLATE DATABASE_DEFAULT = D2.DET_USRCHAR2 COLLATE DATABASE_DEFAULT
                WHERE
                    LEFT(D.DET_HEADER_REF, 3) IN ('RBS', 'RTS', 'ZBS')
                AND D.DET_HEADER_REF COLLATE DATABASE_DEFAULT  NOT IN (SELECT REFERENCE COLLATE DATABASE_DEFAULT FROM OPENQUERY(QRCS11, 'SELECT * FROM PETROC_APP_AGREEMENT_PAYMENT'))";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getChequeName($receiptNumber)
    {
        $sql = 'SELECT
                    TI.NT_USRCHAR3 AS CHEQUE_NAME
                FROM
                    NL_TRANSACTIONS T
                    LEFT OUTER JOIN NL_TRANSACTIONS2 TI ON T.NT_PRIMARY = TI.NT_PRIMARY_2
                WHERE
                    T.NT_HEADER_REF = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$receiptNumber]);
        return $stmt->fetchColumn();
    }

    public function getStudentAgreementPayments()
    {
        $sql = "SELECT DISTINCT
                        SINV.ST_HEADER_REF INVOICE_NUMBER
                ,	NCODE COST_CENTRE
                ,	REPLACE(SINV.ST_HEADER_REF,'AD','') INSTALMENT_REF
                ,	FORMAT(SINV.ST_DATE,'dd/MM/yyyy') INVOICE_DATE
                ,	SINV.ST_GROSS 'Invoice Amount'
                ,	SINV.ST_UNALLOCATED 'Remaining Balance'
                ,	SPAY.ST_HEADER_REF RECEIPT_NUMBER
                ,	SPAY.ST_DESCRIPTION 'Header Description'
                ,	SPAY.ST_DATE 'Payment Date'
                ,	FORMAT(SPAY.ST_DATE,'dd/MM/yyyy') PAYMENT_DATE
                ,	SPAY.ST_COPYCUST as STUDENT_ID
                ,	-AINV.S_AL_VALUE_HOME AMOUNT
                ,       SAG.OBJECT_ID as STUDENT_AGREEMENT
                ,       CASE WHEN LEFT(SPAY.ST_BATCH_REF,2) IN ('C0','C1','C2','C3') THEN
                                'Cash'
                        WHEN LEFT(SPAY.ST_BATCH_REF,1) = 'Q' THEN
                                'Cheque'
                        WHEN LEFT(SPAY.ST_HEADER_REF,4) = 'BACS' THEN
                                'BACS'
                        WHEN LEFT(SPAY.ST_HEADER_REF,2) = 'DD' THEN
                                'Direct Debit'
                        WHEN SPAY.ST_TRANTYPE = 'CRN' THEN
                                'Credit'
                        ELSE
                                'Card'
                        END PAYMENT_METHOD
                FROM SL_TRANSACTIONS SINV
                        INNER JOIN SL_ALLOC_HISTORY AINV ON AINV.S_AL_HEADER_KEY = SINV.ST_HEADER_KEY
                        INNER JOIN SL_ALLOC_HISTORY APAY ON APAY.S_AL_REFERENCE = AINV.S_AL_REFERENCE AND APAY.S_AL_HEADER_KEY <> AINV.S_AL_HEADER_KEY
                        INNER JOIN SL_TRANSACTIONS SPAY ON SPAY.ST_HEADER_KEY = APAY.S_AL_HEADER_KEY
                        INNER JOIN AA_NOMINAL_TRAN_VIEW ON DET_HEADER_KEY = SINV.ST_HEADER_KEY
                        LEFT JOIN QRCS11..MISDEV.PETROC_ORDER_PAYMENT PPAY ON PPAY.RECEIPT_NUMBER = SPAY.ST_HEADER_REF  COLLATE SQL_Latin1_General_CP1_CI_AS
                        JOIN [BPL-REMS-SQL01].PETROCLive.dbo.PETROC_STUDENT_AGREEMENT SAG ON SAG.REFERENCE_NUMBER = REPLACE(SINV.ST_HEADER_REF,'AD','')
                WHERE LEFT(SINV.ST_HEADER_REF,2) = 'AD'
                        AND SINV.ST_YEAR = 'C'
                        AND AINV.S_AL_REFERENCE <> 0
                        AND NCODE NOT LIKE 'ZN%'
                        AND PPAY.OBJECT_ID IS NULL
                        AND SPAY.ST_COPYCUST = '9902836'
                ORDER BY SINV.ST_HEADER_REF
                ,	SPAY.ST_DATE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function addPaymentToInvoice(array $options)
    {
        if ($options['amount'] < 0) {
            return;
        }

        $options['transaction_type'] = 'PAY';
        $this->addAndProcessTransaction($options);

        $id = $this->createTransactionId('SL_AL_ID');
        $allocationOptions = [];
        $allocationOptions['id'] = $id;
        $allocationOptions['lineNumber'] = 1;
        $allocationOptions['reference'] = $options['invoice_ref'];
        $allocationOptions['transactionType'] = 'INV';
        $allocationOptions['amount'] = $options['amount'];

        $this->addAllocationLine($allocationOptions);

        $allocationOptions['lineNumber'] = 2;
        $allocationOptions['reference'] = $options['remittance_learner_ref'];
        $allocationOptions['transactionType'] = $options['transaction_type'];
        $allocationOptions['amount'] = abs($options['amount']);

        $this->addAllocationLine($allocationOptions);

        $this->processCreditRefundNote($id);
    }

    private function addAndProcessTransaction(array $options)
    {
        if ($options['amount'] < 0) {
            return;
        }

        $id = $this->createTransactionId('SL_TRAN_ID');
        $invoiceRef = $options['invoice_ref'];

        $transactionType = 'Payment';
        $batched = 1;
        $nominalCode = 'ZN03-9515';

        $transactionOptions = [
            'id' => $id,
            'lineNumber' => 1,
            'doNotBatch' => $batched,
            'customerReference' => $options['customer_ref'],
            'transactionReference' => $options['remittance_learner_ref'],
            'batchReference' => $options['batch_ref'],
            'note' => $transactionType.' for Invoice ' . $invoiceRef,
            'transactionType' => $options['transaction_type'],
            'headerFlag' => 0,
            'description' => $options['transaction_description'],
            'nominalCode' => $nominalCode,
            'amount' => $options['amount'],
            'taxCode' => TaxCodes::EXEMPT_CODE,
            'heading' => $options['transaction_description'],
            'transactionDescription' => $options['transaction_description']
        ];
        if (isset($options['transaction_period'])) {
            $transactionOptions['transactionPeriod'] = $options['transaction_period'];
        }
        $this->addTransaction($transactionOptions);
        $this->processTransaction($id);
    }

    private function getInvoiceCostCode($invoiceRef)
    {
        $sql = 'SELECT
                    DET_ANALYSIS
                FROM
                    SL_PL_NL_DETAIL
                WHERE
                    DET_HEADER_REF = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$invoiceRef]);
        return $stmt->fetchColumn();
    }

    public function getGymUser()
    {
        $sql = 'select GYM_USER_ID from UDEF_TAB_GYM_USERS where GYM_USER_EMAIL = \'dan.brownhill@petroc.ac.uk\'';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getCustomSequenceNumber($seqPrefix)
    {
        $sql = "SELECT
                    CFT_CUSTOM_SEQUENCE_NEXTNO
                FROM
                    TS_CUSTOM_CUSTOMSEQUENCE_TAB_DATA
                WHERE
                    CFT_CUSTOM_SEQUENCE_SHEET_NAME = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$seqPrefix]);
        $seqNumber = $stmt->fetchColumn();
        
        // If BACS check that header ref hasn't already been used
        if ($seqPrefix === 'BACS') {
            $sql = "SELECT 
                        MAX(SEQUENCE_NO) 
                    FROM (
                        SELECT 
                            MAX(CONVERT(int, REPLACE(NT_HEADER_REF,'BACS',''))) 'SEQUENCE_NO' 
                        FROM 
                            NL_TRANSACTIONS 
                        WHERE 
                            NT_HEADER_REF LIKE 'BACS%'
                        
                        UNION
                        
                        SELECT 
                            MAX(CONVERT(int, REPLACE(ST_HEADER_REF,'BACS',''))) 
                        FROM 
                            SL_TRANSACTIONS 
                        WHERE 
                            ST_HEADER_REF 
                        LIKE 'BACS%'
                    ) A";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $lastSeqNumber = $stmt->fetchColumn();
            if ($lastSeqNumber >= $seqNumber) {
                $seqNumber = $lastSeqNumber + 1;
            }
        }

        $sql = "UPDATE
                    TS_CUSTOM_CUSTOMSEQUENCE_TAB_DATA
                SET
                    CFT_CUSTOM_SEQUENCE_NEXTNO = ?
                WHERE
                    CFT_CUSTOM_SEQUENCE_SHEET_NAME = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$seqNumber + 1, $seqPrefix]);
        return $seqPrefix . strval($seqNumber);
    }
}
