<?php
/**
* Custom payment method in Magento 2
* @category    PaypalPayment
* @package     Apexx_PaypalPayment
*/
namespace Apexx\PaypalPayment\Helper;

use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Framework\DB\Transaction;

class InvoiceGenerate
{
    protected $orderRepository;

    public function __construct(
        InvoiceService $invoiceService,
        InvoiceRepositoryInterface $invoiceRepository,
        Transaction $transaction,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->invoiceService     = $invoiceService;
        $this->invoiceRepository  = $invoiceRepository;
        $this->transaction        = $transaction;
        $this->orderRepository = $orderRepository;
    }
    public function createInvoice($orderId, $amount, $transactionID) {

        $order = $this->orderRepository->get($orderId);
        // Prepare the invoice
        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
        $invoice->setState(Invoice::STATE_PAID);
        $invoice->setBaseGrandTotal($amount);
        $invoice->register();
        $invoice->setTransactionId($transactionID);
        $invoice->getOrder()->setIsInProcess(true);
        $invoice->pay();

        // Create the transaction
        $transactionSave = $this->transaction
            ->addObject($invoice)
            ->addObject($order);
        $transactionSave->save();

        // Update the order
        $order->setTotalPaid($order->getTotalPaid());
        $order->setBaseTotalPaid($order->getBaseTotalPaid());
        $order->setStatus('processing');
        $order->setState('processing');
        $order->save();

        // Save the invoice
        $this->invoiceRepository->save($invoice);
    }

}

