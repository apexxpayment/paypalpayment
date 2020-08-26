<?php
/**
 * Custom payment method in Magento 2
 * @category    PaypalPayment
 * @package     Apexx_PaypalPayment
 */
namespace Apexx\PaypalPayment\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Apexx\PaypalPayment\Helper\Data as PaypalPaymentHelper;

/**
 * Class InvoiceCaptureHandler
 * @package Apexx\PaypalPayment\Gateway\Response
 */
class InvoiceCaptureHandler implements HandlerInterface
{
    const TXN_ID = '_id';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var PaypalPaymentHelper
     */
    protected  $paypalPaymentHelper;

    /**
     * InvoiceCaptureHandler constructor.
     * @param SubjectReader $subjectReader
     * @param PaypalPaymentHelper $paypalPaymentHelper
     */
    public function __construct(
        SubjectReader $subjectReader,
        PaypalPaymentHelper $paypalPaymentHelper
    )
    {
        $this->subjectReader = $subjectReader;
        $this->paypalPaymentHelper = $paypalPaymentHelper;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];

        if (isset($response['status'])) {
            /** @var $payment \Magento\Sales\Model\Order\Payment */
            $payment = $paymentDO->getPayment();
            $payment->setTransactionId($response[self::TXN_ID]);
            $payment->setIsTransactionClosed(false);
            $payment->setTransactionAdditionalInfo('raw_details_info',$response);
        }
    }
}
