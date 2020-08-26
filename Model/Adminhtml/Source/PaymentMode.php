<?php
/**
 * Custom payment method in Magento 2
 * @category    PaypalPayment
 * @package     Apexx_PaypalPayment
 */
namespace Apexx\PaypalPayment\Model\Adminhtml\Source;

/**
 * Class PaymentMode
 * @package Apexx\PaypalPayment\Model\Adminhtml\Source
 */
class PaymentMode
{
    public function toOptionArray()
    {
        return [
                    ['value' => 'TEST', 'label' => __('Test')],
                    ['value' => 'LIVE', 'label' => __('Live')],
        ];
    }
}
