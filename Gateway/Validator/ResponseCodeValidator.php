<?php
/**
 * Custom payment method in Magento 2
 * @category    PaypalPayment
 * @package     Apexx_PaypalPayment
 */
namespace Apexx\PaypalPayment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * Class ResponseCodeValidator
 * @package Apexx\PaypalPayment\Gateway\Validator
 */
class ResponseCodeValidator extends AbstractValidator
{
    /**
     * Performs validation of result code
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $response = $validationSubject['response'];

        $paymentDataObjectInterface = \Magento\Payment\Gateway\Helper\SubjectReader::readPayment($validationSubject);

        if (isset($response['status'])) {
            if ($response['status'] == 'AUTHENTICATION_REQUIRED') {
                $payment = $paymentDataObjectInterface->getPayment();
                $payment->setAdditionalInformation('_id', $response['_id']);
                $payment->setAdditionalInformation('url', $response['url']);

                return $this->createResult(
                    true,
                    []
                );
            } elseif (isset($response['errors'])) {
                if (isset($response['errors'][0]['error_message'])) {
                   return $this->createResult(
                    false,
                    [__($response['errors'][0]['error_message'])]
                    ); 
                }
            } elseif (isset($response['reason_message'])) {
                    return $this->createResult(
                    false,
                    [__($response['reason_message'])]
                    );
            } else {
                return $this->createResult(
                false,
                [__('Gateway rejected the transaction.')]
                );
            }
        }  elseif (isset($response['message'])) {
                return $this->createResult(
                false,
                [__($response['message'])]
                );
        } else {
            return $this->createResult(
                false,
                [__('Gateway rejected the transaction.')]
            );
        }
    }
}
