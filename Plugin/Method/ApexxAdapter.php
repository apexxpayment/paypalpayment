<?php
/**
 * Custom payment method in Magento 2
 * @category    PaypalPayment
 * @package     Apexx_PaypalPayment
 */
namespace Apexx\PaypalPayment\Plugin\Method;

use Magento\Payment\Model\Method\Adapter;
use Magento\Checkout\Model\Session;

/**
 * Class ApexxAdapter
 * @package Apexx\PaypalPayment\Model\Method
 */
class ApexxAdapter
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * ApexxAdapter constructor.
     * @param Session $checkoutSession
     */
    public function __construct(
        Session $checkoutSession
    )
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param Adapter $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetConfigPaymentAction(Adapter $subject, $result)
    {
        $paymentMethod = $this->checkoutSession->getQuote()->getPayment()->getMethodInstance()->getCode();

        if ($paymentMethod == 'paypalpayment_gateway') {
            if ($result == 'authorize_capture') {
                return $result = 'authorize';
            }
        }

        return $result;
    }
}
