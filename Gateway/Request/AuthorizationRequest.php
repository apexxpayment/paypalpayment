<?php
/**
 * Custom payment method in Magento 2
 * @category    PaypalPayment
 * @package     Apexx_PaypalPayment
 */
namespace Apexx\PaypalPayment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Apexx\Base\Helper\Data as ApexxBaseHelper;
use Apexx\PaypalPayment\Helper\Data as PaypalPaymentHelper;
use Magento\Sales\Model\Order;

/**
 * Class AuthorizationRequest
 * @package Apexx\PaypalPayment\Gateway\Request
 */
class AuthorizationRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ApexxBaseHelper
     */
    protected  $apexxBaseHelper;

    /**
     * @var PaypalPaymentHelper
     */
    protected  $paypalPaymentHelper;

    /**
     * AuthorizationRequest constructor.
     * @param ConfigInterface $config
     * @param ApexxBaseHelper $apexxBaseHelper
     * @param PaypalPaymentHelper $paypalPaymentHelper
     */
    public function __construct(
        ConfigInterface $config,
        ApexxBaseHelper $apexxBaseHelper,
        PaypalPaymentHelper $paypalPaymentHelper
    ) {
        $this->config = $config;
        $this->apexxBaseHelper = $apexxBaseHelper;
        $this->paypalPaymentHelper = $paypalPaymentHelper;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */

    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        $order= $payment->getOrder();
        $billing = $order->getBillingAddress();
        $delivery = $order->getShippingAddress();
        $total = $order->getGrandTotalAmount();

        $requestData= [
            //"account" => $this->apexxBaseHelper->getAccountId(),
            //"account" => '71eafe8d7b3f476b9e6d34fd3a53b9a2',
            "organisation" => $this->apexxBaseHelper->getOrganizationId(),
            "capture_now" => $this->paypalPaymentHelper->getHostedPaymentAction(),
            "customer_ip" => $order->getRemoteIp(),
            "recurring_type" => $this->paypalPaymentHelper->getRecurringType(),
            "amount" => ($total * 100),
            "currency" => $order->getCurrencyCode(),
            "user_agent" => $this->apexxBaseHelper->getUserAgent(),
            "locale" => $this->apexxBaseHelper->getStoreLocale(),
            "dynamic_descriptor" => $this->paypalPaymentHelper->getDynamicDescriptor(),
            "merchant_reference" => 'JOURNEYBOX'.$order->getOrderIncrementId(),
            "payment_product_type" => $this->paypalPaymentHelper->getPaymentProductType(),
            "shopper_interaction" => $this->paypalPaymentHelper->getShopperInteraction(),
        ];

        $totalQty = 0;
        $totalTaxAmount = 0;
        foreach ($order->getItems() as $item) {
            $totalQty = $totalQty + $item->getQtyOrdered();
            $totalTaxAmount = $totalTaxAmount + $item->getTaxAmount();
        }

        $paypalDataFields=[];
        $paypalDataFields['paypal']['brand_name'] = $this->paypalPaymentHelper->getPaypalBrandName();
        $paypalDataFields['paypal']['customer_paypal_id'] = $this->paypalPaymentHelper->getCustomerPaypalId();
        $paypalDataFields['paypal']['tax_id'] = $this->paypalPaymentHelper->getPaypalTaxId();
        $paypalDataFields['paypal']['tax_id_type'] = $this->paypalPaymentHelper->getPaypalTaxTypeId();
        $paypalDataFields['paypal']['order']['invoice_number'] = 'invoice'.$order->getOrderIncrementId();
        $paypalDataFields['paypal']['order']['total_tax_amount'] = $totalTaxAmount;
        $paypalDataFields['paypal']['order']['description'] = $this->paypalPaymentHelper->getOrderDescription();

        foreach ($order->getItems() as $item) {
            $paypalDataFields['paypal']['order']['items'][] = [
                'item_name' => $item->getName(),
                'unit_amount' =>  ($item->getPrice() - $item->getDiscountAmount()) * 100,
                "currency" => $order->getCurrencyCode(),
                "tax_currency" => $order->getCurrencyCode(),
                "tax_amount" => $item->getTaxAmount(),
                "quantity" => $item->getQtyOrdered(),
                "item_description" => $item->getName(),
                "sku" => $item->getSku()
            ];
        }

        $paypalDataFields['paypal']['redirection_parameters']['return_url'] = $this->paypalPaymentHelper->getReturnUrl();

        $customerFields=[];
        $customerFields['customer']['first_name'] = $billing->getFirstname();
        $customerFields['customer']['last_name'] = $billing->getLastname();
        $customerFields['customer']['email'] = $billing->getEmail();
        $customerFields['customer']['phone'] = $billing->getTelephone();
        $customerFields['customer']['date_of_birth'] = $this->paypalPaymentHelper->getCustomerInfoDob();
        $customerFields['customer']['address'] = [
            "address" =>  $billing->getStreetLine1().''.$billing->getStreetLine2(),
			"city" => $billing->getCity(),
			"state" => $billing->getRegionCode(),
			"postal_code" => $billing->getPostcode(),
			"country" => $billing->getCountryId()
        ];

        $deliveryCustomerFields=[];
        $deliveryCustomerFields['delivery_customer']['first_name'] = $delivery->getFirstname();
        $deliveryCustomerFields['delivery_customer']['last_name'] = $delivery->getLastname();
        $deliveryCustomerFields['delivery_customer']['address'] = [
            "address" =>  $delivery->getStreetLine1().''.$delivery->getStreetLine2(),
            "city" => $delivery->getCity(),
            "state" => $delivery->getRegionCode(),
            "postal_code" => $delivery->getPostcode(),
            "country" => $delivery->getCountryId()
        ];

        $requestData = array_merge($requestData, $paypalDataFields, $customerFields, $deliveryCustomerFields);

        return $requestData;
    }
}
