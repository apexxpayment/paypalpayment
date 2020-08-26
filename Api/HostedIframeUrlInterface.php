<?php
/**
 * Custom payment method in Magento 2
 * @category    PaypalPayment
 * @package     Apexx_PaypalPayment
 */

namespace Apexx\PaypalPayment\Api;


interface HostedIframeUrlInterface
{
    /**
     * @param string $orderId
     * @return string
     */
    public function getHostedIframeUrl($orderId);
}
