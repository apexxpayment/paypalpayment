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
use Apexx\PaypalPayment\Helper\Data as PaypalPaymentHelper;

/**
 * Class CancelHandler
 * @package Apexx\PaypalPayment\Gateway\Response
 */
class CancelHandler implements HandlerInterface
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
     * CancelHandler constructor.
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

        $payment = $paymentDO->getPayment();

        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment->setTransactionId($response[self::TXN_ID]);
        $payment->setIsTransactionClosed(true);
        $payment->setShouldCloseParentTransaction(true);
        $payment->setTransactionAdditionalInfo('raw_details_info',$response);
    }
}
