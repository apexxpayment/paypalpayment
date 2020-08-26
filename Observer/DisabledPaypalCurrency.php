<?php
/**
 * Custom payment method in Magento 2
 * @category    PaypalPayment
 * @package     Apexx_PaypalPayment
 */
namespace Apexx\PaypalPayment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Model\Session As CheckoutSession;
use Apexx\PaypalPayment\Helper\Data As PaypalPaymentHelper;

/**
 * Class DisabledPaypalCurrency
 * @package Apexx\PaypalPayment\Observer
 */
class DisabledPaypalCurrency implements ObserverInterface
{
    /**
     * @var Session
     */
	protected $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var PaypalPaymentHelper
     */
    protected $paypalPaymentHelper;

    /**
     * DisabledPaypalCurrency constructor.
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CartRepositoryInterface $quoteRepository
     * @param CheckoutSession $checkoutSession
     * @param PaypalPaymentHelper $paypalPaymentHelper
     */
	public function __construct(
	    Session $customerSession,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CartRepositoryInterface $quoteRepository,
        CheckoutSession $checkoutSession,
        PaypalPaymentHelper $paypalPaymentHelper
    ) {
		$this->customerSession = $customerSession;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->paypalPaymentHelper = $paypalPaymentHelper;
	}

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
	public function execute(\Magento\Framework\Event\Observer $observer)
    {
    $paymentMethod = $observer->getEvent()->getMethodInstance()->getCode();
        $result = $observer->getEvent()->getResult();

        $quoteCurrency = $this->checkoutSession->getQuote()->getQuoteCurrencyCode();
        $allowCurrency = $this->paypalPaymentHelper->getAllowPaymentCurrency($quoteCurrency); 

        if ($this->customerSession->isLoggedIn()) {
            if ($paymentMethod == 'paypalpayment_gateway') {
                if (!empty($allowCurrency)) {
                    $result->setData('is_available', true);
                    return;
                } else {
                    $result->setData('is_available', false);
                    return;
                }
            }
        } else {
            if ($paymentMethod == 'paypalpayment_gateway') {
             if (!empty($allowCurrency)) {
                    $result->setData('is_available', true);
                    return;
                } else {
                    $result->setData('is_available', false);
                    return;
                }
            }
        }
    }
}
