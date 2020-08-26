<?php
/**
 * Custom payment method in Magento 2
 * @category    PaypalPayment
 * @package     Apexx_PaypalPayment
 */
namespace Apexx\PaypalPayment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Payment\Gateway\Http\ClientException;
use Apexx\Base\Helper\Data as ApexxBaseHelper;
use Apexx\PaypalPayment\Helper\Data as PaypalPaymentHelper;
use Apexx\Base\Helper\Logger\Logger as CustomLogger;

/**
 * Class ClientMock
 * @package Apexx\PaypalPayment\Gateway\Http\Client
 */
class ClientMock implements ClientInterface
{
    const SUCCESS = 1;
    const FAILURE = 0;

    /**
     * @var array
     */
    private $results = [
        self::SUCCESS,
        self::FAILURE
    ];
    /**
     * @var Curl
     */
    protected $curlClient;

    /**
     * @var ApexxBaseHelper
     */
    protected  $apexxBaseHelper;

    /**
     * @var PaypalPaymentHelper
     */
    protected  $paypalPaymentHelper;

    /**
     * @var CustomLogger
     */
    protected $customLogger;

    /**
     * ClientMock constructor.
     * @param Curl $curl
     * @param ApexxBaseHelper $apexxBaseHelper
     * @param PaypalPaymentHelper $paypalPaymentHelper
     * @param CustomLogger $customLogger
     */
    public function __construct(
        Curl $curl,
        ApexxBaseHelper $apexxBaseHelper,
        PaypalPaymentHelper $paypalPaymentHelper,
        CustomLogger $customLogger
    ) {
        $this->curlClient = $curl;
        $this->apexxBaseHelper = $apexxBaseHelper;
        $this->paypalPaymentHelper = $paypalPaymentHelper;
        $this->customLogger = $customLogger;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $url = $this->apexxBaseHelper->getApiEndpoint().'payment';

        $resultCode = json_encode($transferObject->getBody());

        $response = $this->apexxBaseHelper->getCustomCurl($url, $resultCode);

        $resultObject = json_decode($response);
        $responseResult = json_decode(json_encode($resultObject), True);

        $this->customLogger->debug('Paypal Authorize Request:', $transferObject->getBody());
        $this->customLogger->debug('Paypal Authorize Response:', $responseResult);

        $resultObject = json_decode($response,True);

        if (isset($resultObject['status'])) {
            if ($resultObject['status'] == 'AUTHENTICATION_REQUIRED') {
                return $responseResult;
            } elseif ($resultObject['status'] == 'FAILED') {
                if (isset($resultObject['reason_message'])) {
                    throw new ClientException(__($resultObject['reason_message']));
                } else {
                    throw new ClientException(__('A server error stopped your order from being placed.'));
                }
            } elseif ($resultObject['status'] == 'DECLINED') {
                if (isset($resultObject['reason_message'])) {
                    throw new ClientException(__($resultObject['reason_message']));
                } else {
                    throw new ClientException(__('A server error stopped your order from being placed.'));
                }
            } elseif (isset($resultObject['message'])) {
                throw new ClientException(__($resultObject['message']));
            } else {
                throw new ClientException(__('A server error stopped your order from being placed.'));
            }
        } else {
            throw new ClientException(__('A server error stopped your order from being placed.'));
        }
    }
}
