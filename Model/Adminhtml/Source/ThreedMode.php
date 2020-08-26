<?php
/**
 * Custom payment method in Magento 2
 * @category    PaypalPayment
 * @package     Apexx_PaypalPayment
 */
namespace Apexx\PaypalPayment\Model\Adminhtml\Source;

/**
 * Class ThreedMode
 * @package Apexx\PaypalPayment\Model\Adminhtml\Source
 */
class ThreedMode
{
    public function toOptionArray()
    {
        return [
                    ['value' => 'sca', 'label' => __('sca (sca)')],
                    ['value' => 'frictionless', 'label' => __('frictionless (frictionless)')],
        ];
    }
}
