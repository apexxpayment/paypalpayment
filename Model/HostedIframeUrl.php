<?php
/**
 * Custom payment method in Magento 2
 * @category    PaypalPayment
 * @package     Apexx_PaypalPayment
 */

namespace Apexx\PaypalPayment\Model;

use \Magento\Sales\Api\OrderRepositoryInterface;
use Apexx\PaypalPayment\Model\Ui\ConfigProvider;
use Apexx\PaypalPayment\Helper\Data as PaypalPaymentHelper;
use Psr\Log\LoggerInterface;

/**
 * Class HostedIframeUrl
 * @package Apexx\PaypalPayment\Model
 */
class HostedIframeUrl implements \Apexx\PaypalPayment\Api\HostedIframeUrlInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var PaypalPaymentHelper
     */
    protected  $paypalPaymentHelper;

    /**
     * Logger for exception details
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * HostedIframeUrl constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param PaypalPaymentHelper $paypalPaymentHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        PaypalPaymentHelper $paypalPaymentHelper,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->paypalPaymentHelper = $paypalPaymentHelper;
        $this->logger = $logger;
    }

    /**
     * @param string $orderId
     * @return array|false|string
     */
    public function getHostedIframeUrl($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $payment = $order->getPayment();
        $response = [];
        try {
            if ($payment->getMethod() === 'paypalpayment_gateway') {
                $additionalInformation = $payment->getAdditionalInformation();
                $iframeUrl = $additionalInformation['url'];
                $response['url'] = $iframeUrl;
            }
            return json_encode($response);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $response;
    }
}
