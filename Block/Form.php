<?php
/**
 * Custom payment method in Magento 2
 *
 * @category PaypalPayment
 * @package  Apexx_PaypalPayment
 */
namespace Apexx\PaypalPayment\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Session as customerSession;
use Magento\Directory\Model\Currency;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class Form
 * @package Apexx\PaypalPayment\Block
 */
class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Order
     */
    protected $objOrder;

    /**
     * @var customerSession
     */
    protected $objCustomerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $objStoreManagerInterface;
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * Form constructor.
     * @param Context $context
     * @param Order $order
     * @param customerSession $customerSession
     * @param StoreManagerInterface $storeManagerInterface
     * @param Session $checkoutSession
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        Context $context,
        Order $order,
        customerSession $customerSession,
        StoreManagerInterface $storeManagerInterface,
        Session $checkoutSession,
        SessionManagerInterface $sessionManager
    ) {
        parent::__construct($context);
        $this->objOrder = $order;
        $this->objCustomerSession = $customerSession;
        $this->objStoreManagerInterface = $storeManagerInterface;
        $this->checkoutSession = $checkoutSession;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @return array
     */
    public function getResponseParams()
    {
        return $this->getRequest()->getParams();
    }

    /**
     * @param $orderId
     * @return Order
     */
    public function getOrderDetails($orderId)
    {
        return $this->objOrder->loadByIncrementId($orderId);
    }

    /**
     * @return customerSession
     */
    public function getCustomerDetail()
    {
        return $this->objCustomerSession;
    }

    /**
     * @return mixed
     */
    public function getPaypalFailureMessage()
    {
        $this->sessionManager->start();
        return $this->sessionManager->getData();
    }
}
