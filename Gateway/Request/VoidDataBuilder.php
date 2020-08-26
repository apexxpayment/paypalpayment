<?php
/**
 * Custom payment method in Magento 2
 * @category    PaypalPayment
 * @package     Apexx_PaypalPayment
 */
namespace Apexx\PaypalPayment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class VoidDataBuilder
 * @package Apexx\PaypalPayment\Gateway\Request
 */
class VoidDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        /** @var Payment $orderPayment */
        $orderPayment = $paymentDO->getPayment();
        //$orderPayment->setIsTransactionClosed(true);
        //$orderPayment->setShouldCloseParentTransaction(true);

        // Send Parameters to Paypal Payment Client
        $order = $paymentDO->getOrder();
        $total = $order->getGrandTotalAmount();

        $formFields=[];
        $requestData = [
            "transactionId" => $orderPayment->getLastTransId(),
            "gross_amount" => ($total * 100),
        ];

        $totalQty = 0;
        foreach ($order->getItems() as $item) {
            $totalQty = $totalQty + $item->getQtyOrdered();
        }

        foreach ($order->getItems() as $item) {
            $formFields['items'][] = [
                'product_id' => $item->getProductId(),
                'item_description' => $item->getName(),
                'gross_unit_price' => ($item->getPrice() - $item->getDiscountAmount()) * 100,
                'quantity' => (int)$item->getQtyOrdered()
            ];
        }

        $requestData = array_merge($requestData, $formFields);

        return $requestData;
    }
}
