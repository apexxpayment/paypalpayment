<?php
/**
 * Custom payment method in Magento 2
 * @category    PaypalPayment
 * @package     Apexx_PaypalPayment
 */
namespace Apexx\PaypalPayment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Apexx\PaypalPayment\Gateway\Http\Client\ClientMock;

/**
 * Class ConfigProvider
 * @package Apexx\PaypalPayment\Model\Ui
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'paypalpayment_gateway';

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'transactionResults' => [
                        ClientMock::SUCCESS => __('Success'),
                        ClientMock::FAILURE => __('Fraud')
                    ]
                ]
            ]
        ];
    }
}
