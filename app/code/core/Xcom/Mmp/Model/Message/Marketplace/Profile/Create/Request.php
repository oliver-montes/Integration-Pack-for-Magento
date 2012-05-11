<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Xcom
 * @package     Xcom_Mmp
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Mmp_Model_Message_Marketplace_Profile_Create_Request extends Xcom_Xfabric_Model_Message_Request
{
    /**
     * Shipping local types
     */
    const SHIPPING_LOCAL_TYPE_DOMESTIC = "DOMESTIC";
    const SHIPPING_LOCAL_TYPE_INTERNATIONAL = "INTERNATIONAL";

    /** @var array payment types accepted by integration */
    protected $_paymentTypes = array("AMEX", "CASH_ON_DELIVERY", "CHECK", "CREDIT_CARD",
                                     "DINERS", "DISCOVER", "ESCROW", "INTEGRATED_MERCHANT_CREDIT_CARD",
                                     "MASTERCARD", "MONEY_ORDER", "MONEY_TRANSFER", "MONEY_TRANSFER_IN_CHECKOUT",
                                     "MONEYBOOKERS", "PAYMATE", "PAYMENT_ON_PICKUP", "PAYMENT_SEE_DESCRIPTION",
                                     "PAYPAL", "PROPAY", "VISA");

    protected $_storeId = null;

    /**
     * @var Xcom_Mmp_Model_Policy
     */
    protected $_policy;

    /**
     * @var Xcom_Mmp_Model_Channel
     */
    protected $_channel;

    protected function _construct()
    {
        parent::_construct();
        $this->_topic = 'marketplace/profile/create';
        $this->_schemaRecordName = 'CreateProfile';
    }
    /**
     * All actions that need to retrieve data for message
     *
     * @param Varien_Object $dataObject
     *
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    public function _prepareData(Varien_Object $dataObject = null)
    {
        $this->addCorrelationId();

        $this->_policy  = $dataObject->getPolicy();
        if (!is_object($this->_policy)) {
            throw Mage::exception('Mage_Core', Mage::helper('xcom_mmp')->__('Policy should be specified.'));
        }
        $this->_channel = $dataObject->getChannel();
        if (!is_object($this->_channel)) {
            throw Mage::exception('Mage_Core', Mage::helper('xcom_mmp')->__('Channel should be specified.'));
        }

        $data = array(
            'p' => array(
                'xId'             => $this->getXprofileId(),
                'name'            => $this->getPolicy()->getName(),
                'siteCode'        => $this->getChannel()->getSiteCode(),
                'xAccountId'      => $this->_prepareCredentials(),
                'payment'         => $this->_preparePayment(),
                'shipping'        => $this->_prepareShipping(),
                'returnPolicy'    => $this->_prepareReturnPolicy(),
                'embeddedMessage' => $this->_prepareEmbeddedMessage()
            )
        );

        $this->getPolicy()
            ->setCorrelationId($this->getCorrelationId());

        $this->setMessageData($data);
        return parent::_prepareData($dataObject);
    }

    /**
     * @return Xcom_Mmp_Model_Channel
     */
    public function getChannel()
    {
        return $this->_channel;
    }

    /**
     * Retrieve marketplace policy id
     *
     * @return null
     */
    public function getXprofileId()
    {
        return null;
    }

    /**
     * Get policy object from options
     *
     * @return null|Xcom_Mmp_Model_Resource_Policy
     */
    public function getPolicy()
    {
        return $this->_policy;
    }

    /**
     * Returns shipping services which is supported by given channel.
     *
     * @return array
     */
    public function getShippingServices()
    {
        return Mage::getResourceModel('xcom_mmp/shippingService')->getShippingServices($this->getChannel());
    }

    /**
     * Prepare Credentials, auth id
     *
     * @return string
     */
    protected function _prepareCredentials()
    {
        return $this->getChannel()->getXaccountId();
    }

    /**
     * Prepare shipping for the product based on policy
     * @return array|null
     */
    protected function _prepareShipping()
    {
        if (!$this->getPolicy() || count($this->getPolicy()->getShippingData()) == 0) {
            return null;
        }

        $shipping = array();
        foreach($this->getShippingServices() as $shippingData) {
            foreach($this->getPolicy()->getShippingData() as $policyShippingData){
                if ($policyShippingData['shipping_id'] != $shippingData['shipping_id']) {
                    continue;
                }
                $rateType               = 'FLAT';

                $keyCode = $rateType . self::SHIPPING_LOCAL_TYPE_DOMESTIC;
                if (!isset($shipping[$keyCode])) {
                    $shipping[$keyCode] = array(
                        'rateType'        => $rateType,
                        'localeType'      => self::SHIPPING_LOCAL_TYPE_DOMESTIC,
                        'applyPromotionalShippingRule'  => false,
                        'shippingServiceOptions'        => array()
                    );
                }

                $shipping[$keyCode]['shippingServiceOptions'][] = array(
                    'sellerPriority'        => (int)$policyShippingData['sort_order'],
                    'serviceName'           => $shippingData['service_name'],
                    'cost'                  => $this->_getCurrencyAmount($policyShippingData['cost']),
                    'additionalCost'        => null,
                    'packagingHandlingCost' => null,
                    'surcharge'             => null,
                    'shipToLocations'       => null,
                    'discountAmount'        => null
                );
            }
        }

        return array('shippingLocaleServices' => array_values($shipping));
    }

     /**
     * Prepare return policy
     * @return array|null
     */
    protected function _prepareReturnPolicy()
    {
        $returnPolicy   = null;
        $policy         = $this->getPolicy();
        $channelCode    = $this->getChannel()->getSiteCode();
        if (!is_null($policy)) {
            $returnPolicy = array(
                'description'               => null,
                'returnAccepted'            => false,
                'buyerPaysReturnShipping'   => null,
                'returnByDays'              => null,
                'refundMethod'              => null
            );
            if ($policy->getReturnAccepted()) {
                $returnPolicy['returnAccepted'] = true;
                if (Mage::helper('xcom_mmp/channel')->showFullReturnPolicyData($channelCode)) {
                    $returnPolicy['description']    = $policy->getReturnDescription();
                    if ($policy->getShippingPaidBy() !== null) {
                        $returnPolicy['buyerPaysReturnShipping'] = ($policy->getShippingPaidBy() == 'buyer')
                                ? true
                                : false;
                    }
                    if ($policy->getReturnByDays() !== null) {
                        $returnPolicy['returnByDays']   = (int) $policy->getReturnByDays();
                    }
                    if ($policy->getRefundMethod() !== null) {
                        $returnPolicy['refundMethod']   = $policy->getRefundMethod();
                    }
                }
            }
        }
        return $returnPolicy;
    }

    /**
     * Prepare payment information based on policy
     * @return array|null
     */
    protected function _preparePayment()
    {
        $payment = null;
        $policy = $this->getPolicy();
        if ($policy && !is_null($policy->getPaymentName())) {
            if (!is_array($policy->getPaymentName())) {
                $paymentName = explode(',', $policy->getPaymentName());
            } else {
                $paymentName = $policy->getPaymentName();
            }
            $acceptedPayment = array_intersect($paymentName, $this->_paymentTypes);
            $payment = array(
                'acceptedPaymentTypes' => $acceptedPayment,
                'immediatePaymentRequired' => null, /**@TODO: add real data immediatePaymentRequired*/
                'paymentInstructions' => null /**@TODO: add real data paymentInstructions*/
            );
        }
        return $payment;
    }

    /**
     * Retrieve embeddedMessage data
     *
     * @return string
     */
    protected function _prepareEmbeddedMessage()
    {
        $options = array(
            'location'              => null,
            'postalCode'            => $this->getPolicy()->getData('postal_code'),
            'countryCode'           => $this->getPolicy()->getData('location'),
            'payPalEmailAddress'    => $this->getPolicy()->getData('payment_paypal_email'),
            'handlingTime'          => $this->getPolicy()->getData('handling_time'),
            'useTaxTable'           => (bool)$this->getPolicy()->getData('apply_tax')
        );

        $payload = $this->_getMarketSpecificsMessage()
          ->process(new Varien_Object($options))
          ->getMessageData();

        $body   = array(
            'schemaVersion' => '1.0.0',
            'schemaUri'     => null,
            'payload'       => $payload
        );

        return $body;
    }

    public function encode()
    {
        $this->_initSchema();
        $data = $this->getMessageData();
        if (isset($data['p']['embeddedMessage']['payload'])) {
            $data['p']['embeddedMessage']['payload'] = $this->getEncoder()->encodeText(
                $data['p']['embeddedMessage']['payload'],
                $this->_getMarketSpecificsMessage()
            );

        }
        $this->setBody($data);
        $this->getEncoder()->encode($this);
        return $this;
    }

    /**
     * @return Xcom_Mmp_Model_Message_Marketplace_Profile_Specifics_Request
     */
    protected function _getMarketSpecificsMessage()
    {
        return Mage::helper('xcom_xfabric')->getMessage('marketplace/profile/marketSpecifics');
    }

    /**
     * Prepare CurrencyAmount object
     * @param $amount
     * @return array
     */
    protected function _getCurrencyAmount($amount)
    {
        return array(
            'amount'    => (double)$amount,
            'code'      => $this->_getCurrencyCode()
        );
    }

    /**
     * Get currency code
     *
     * @return string
     */
    protected function _getCurrencyCode()
    {
        $policy         = $this->getPolicy();
        //TODO from what fields we get currency code and do we have to do cost conversion
        $currencyCode   = ($policy && $policy->getCurrency())
                ? $policy->getCurrency()
                :(string)$this->_getStore()->getBaseCurrencyCode();
        return $currencyCode;
    }

    /**
     * Load store by current channel
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        return Mage::app()->getStore($this->_storeId);
    }
}
