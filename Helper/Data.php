<?php
/**
 * Custom payment method in Magento 2
 * @category    PaypalPayment
 * @package     Apexx_PaypalPayment
 */
namespace Apexx\PaypalPayment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface ;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json as SerializeJson;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\HTTP\Header as HttpHeader;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class Data
 * @package Apexx\PaypalPayment\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Config paths
     */
    const XML_CONFIG_PATH_PAYPALPAYMENT  = 'payment/paypalpayment_gateway';
    const XML_PATH_PAYMENT_PAYPALPAYMENT = 'payment/apexx_section/apexxpayment/paypalpayment_gateway';
    const XML_PATH_RETURN_URL            = '/redirect_url';
    const XML_PATH_PAYMENT_ACTION        = '/payment_action';
    const XML_PATH_DYNAMIC_DESCRIPTOR    = '/dynamic_descriptor';
    const XML_PATH_CURRANCY              = '/currency';
    const XML_PATH_CAPTURE_MODE          = '/capture_mode';
    const XML_PATH_PAYMENT_MODES         = '/payment_modes';
    const XML_PATH_PAYMENT_TYPE          = '/payment_type';
    const XML_PATH_RECURRING_TYPE        = '/recurring_type';
    const XML_PATH_PAYMENT_PTYPE         = '/payment_product_type';
    const XML_PATH_SHOPPER_INTERACTION   = '/shopper_interaction';
    const XML_PATH_BRAND_NAME            = '/brand_name';
    const XML_PATH_CUSTOMER_PAYPAL_ID    = '/customer_paypal_id';
    const XML_PATH_PAYPAL_TAX_ID         = '/tax_id';
    const XML_PATH_PAYPAL_TAX_TYPE_ID    = '/tax_id_type';
    const XML_PATH_CUSTOMER_DOB          = '/customer_dob';
    const XML_PATH_ORDER_DESCP           = '/order_descp';
    const XML_PATH_ALLOW_CURRENCY        = '/allow';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var SerializeJson
     */
    protected $serializeJson;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var HttpHeader
     */
    protected $httpHeader;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @var SessionFactory
     */
    protected $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Data constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param JsonFactory $resultJsonFactory
     * @param SerializeJson $serializeJson
     * @param CurlFactory $curlFactory
     * @param HttpHeader $httpHeader
     * @param OrderRepository $orderRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param SearchCriteriaBuilder $searchBuilder
     * @param FilterBuilder $filterBuilder
     * @param SessionFactory $customerSession
     * @param CustomerRepositoryInterface
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        JsonFactory $resultJsonFactory,
        SerializeJson $serializeJson,
        curlFactory $curlFactory,
        HttpHeader $httpHeader,
        OrderRepository $orderRepository,
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchBuilder,
        FilterBuilder $filterBuilder,
        SessionFactory $customerSession,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor ;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serializeJson = $serializeJson;
        $this->curlFactory = $curlFactory;
        $this->httpHeader = $httpHeader;
        $this->orderRepository  = $orderRepository;
        $this->transactionRepository = $transactionRepository;
        $this->searchBuilder = $searchBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getConfigPathValue($key)
    {
        return $this->scopeConfig->getValue(
            self::XML_CONFIG_PATH_PAYPALPAYMENT . $key,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get config value at the specified key
     *
     * @param string $key
     * @return mixed
     */
    public function getConfigValue($key)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_PAYPALPAYMENT . $key,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getReturnUrl()
    {
         return $this->getConfigValue(self::XML_PATH_RETURN_URL);
    }

    /**
     * @return string
     */
    public function getHostedPaymentAction()
    {
        $hostPaymentAction = $this->getConfigPathValue(self::XML_PATH_PAYMENT_ACTION);
        if ($hostPaymentAction == 'authorize') {
            return 'false';
        } else {
            return 'true';
        }
    }

    /**
     * @return mixed
     */
    public function getDynamicDescriptor()
    {
        return $this->getConfigPathValue(self::XML_PATH_DYNAMIC_DESCRIPTOR);
    }

    /**
     * @return mixed
     */
    public function getRecurringType()
    {
        return $this->getConfigPathValue(self::XML_PATH_RECURRING_TYPE);
    }

    /**
     * @return mixed
     */
    public function getPaymentProductType()
    {
        return $this->getConfigPathValue(self::XML_PATH_PAYMENT_PTYPE);
    }

    /**
     * @return mixed
     */
    public function getShopperInteraction()
    {
        return $this->getConfigPathValue(self::XML_PATH_SHOPPER_INTERACTION);
    }

    /**
     * @return mixed
     */
    public function getPaypalBrandName()
    {
        return $this->getConfigPathValue(self::XML_PATH_BRAND_NAME);
    }

    /**
     * @return mixed
     */
    public function getCustomerPaypalId()
    {
        return $this->getConfigValue(self::XML_PATH_CUSTOMER_PAYPAL_ID);
    }

    /**
     * @return string
     */
    public function getCustomPaymentType()
    {
        return $this->getConfigValue(self::XML_PATH_PAYMENT_TYPE);
    }

    /**
     * @return mixed
     */
    public function getPaypalTaxId()
    {
        return $this->getConfigValue(self::XML_PATH_PAYPAL_TAX_ID);
    }

    /**
     * @return mixed
     */
    public function getPaypalTaxTypeId()
    {
        return $this->getConfigValue(self::XML_PATH_PAYPAL_TAX_TYPE_ID);
    }

    /**
     * @return mixed
     */
    public function getCustomerInfoDob()
    {
        return $this->getConfigValue(self::XML_PATH_CUSTOMER_DOB);
    }

    /**
     * @return mixed
     */
    public function getOrderDescription()
    {
        return $this->getConfigValue(self::XML_PATH_ORDER_DESCP);
    }

    /**
     * @param $currency
     * @return array
     */
    public function getAllowPaymentCurrency($currency) {
        $allowCurrencyList = $this->getConfigValue(self::XML_PATH_ALLOW_CURRENCY);
        if (!empty($allowCurrencyList)) {
            $currencyList = explode(",", $allowCurrencyList);
            if (!empty($currencyList)) {
                $currencyInfo = [];
                foreach ($currencyList as $key => $value) {
                    if ($value == $currency) {
                        $currencyInfo['currency_code'] = $value;
                    }
                }

                return $currencyInfo;
            }
        }
    }

    /**
     * @param $orderId
     * @return float|null
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getOrderTotalInvoiced($orderId) {
        $order = $this->orderRepository->get($orderId);
        return $order->getTotalInvoiced();
    }
}
