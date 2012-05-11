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
 * @package     Xcom_ChannelOrder
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_ChannelOrder_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @var Xcom_ChannelOrder_Model_Order
     */
    protected $_channelOrder;

    const CHANNELORDER_VALIDATION_SETTINGS_SANDBOX_ENABLED =
        'channelorder/validation_settings/sandbox_orders_allowed';

    const PRODUCTION_ENV_LITERAL = 'production';

    /**
     * @return bool
     */
    public function isChannelOrder()
    {
        return (bool)$this->getChannelOrder()->getId();
    }

    /**
     * @return Xcom_ChannelOrder_Model_Order
     */
    public function getChannelOrder()
    {
        if (null === $this->_channelOrder) {
            $this->_channelOrder = Mage::getModel('xcom_channelorder/order');
            if ($this->getOrder()) {
                $this->_channelOrder->load($this->getOrder()->getId(), 'order_id');
            }
            elseif ($this->getInvoice()) {
                $this->_channelOrder->load($this->getInvoice()->getOrder()->getId(), 'order_id');
            }
            elseif ($this->getCreditmemo()) {
                $this->_channelOrder->load($this->getCreditmemo()->getOrder()->getId(), 'order_id');
            }
        }
        return $this->_channelOrder;
    }

    /**
     * The Magento item is used to identify channel order item.
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return bool
     */
    public function getChannelOrderItem(Mage_Sales_Model_Order_Item $item)
    {
        foreach ($this->getChannelOrder()->getOrderItems() as $channelItem) {
            if ($item->getProductId() == $channelItem->getOrderItemId()) {
                return $channelItem;
            }
        }
        return false;
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function getInvoice()
    {
        return Mage::registry('current_invoice');
    }

    /**
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function getCreditmemo()
    {
        return Mage::registry('current_creditmemo');
    }

    /**
     * @return string
     */
    public function getChannelAccountHtml()
    {
        $xAccountId = $this->getChannelOrder()->getXaccountId();
        if (!$xAccountId) {
            return '';
        }
        $account = Mage::getModel('xcom_mmp/account')->load($xAccountId, 'xaccount_id');
        return sprintf('%s (%s)', $account->getUserId(), $account->getEnvironmentValue());
    }

    /**
     * Returns order's channel name.
     *
     * @return string
     */
    public function getChannelName()
    {
        $channelId = $this->getChannelOrder()->getChannelId();
        $channel = Mage::getModel('xcom_mmp/channel')->load($channelId);
        return $channel->getName();
    }

    /**
     * Validate whether an order originated from production environment
     *
     * @param $accountId
     * @return Xcom_ChannelOrder_Helper_Data
     * @throws Mage_Core_Exception
     */
    public function validateOrderEnvironment($accountId)
    {
        if (empty($accountId)) {
            $message = $this->__('No accountId in the message');
            throw Mage::exception('Xcom_ChannelOrder', $message);
        }

        if (Mage::getStoreConfigFlag(self::CHANNELORDER_VALIDATION_SETTINGS_SANDBOX_ENABLED)) {
            return true;
        }

        $environment = Mage::getModel('xcom_mmp/account')
            ->load($accountId, 'xaccount_id')
            ->getEnvironmentValue();

        if (strtolower($environment) != self::PRODUCTION_ENV_LITERAL) {
            $message = $this->__('Account specified for order(s) is not of production environment. Skipped.');
            throw Mage::exception('Xcom_ChannelOrder', $message);
        }
        return true;
    }

    /**
     * Validate if data address have required value for address
     *
     * @param array $data
     * @return bool
     */
    public function isValidAddress(array $data)
    {
        if (empty($data['street'])) {
            return false;
        }
        return true;
    }

    /**
     * Remove commas as group separators from acmount values
     * Ex. 1,234.00 -> 1234.00
     *
     * @param $data
     */
    public function prepareSourceAmounts(&$data)
    {
        foreach ($data as $key => &$val) {
            if (is_array($val)) {
                $this->prepareSourceAmounts($val);
            }
            elseif (is_string($val)) {
                if ($key == 'amount') {
                    $val = str_replace(',', '', $val);
                }
            }
        }
    }
}
