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
class Xcom_ChannelOrder_Model_Observer
{
    /**
     * Check Available reordering order
     *
     * Method for event "controller_action_predispatch_adminhtml_sales_order_create_reorder"
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function checkAvailableReorder(Varien_Event_Observer $observer)
    {
        /** @var $controller Mage_Adminhtml_Sales_Order_CreateController */
        $controller = $observer->getData('controller_action');

        $orderId = $controller->getRequest()->getParam('order_id');
        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($orderId);
        if ($order->getId()) {
            /** @var $helper Xcom_ChannelOrder_Helper_Data */
            $helper = Mage::helper('xcom_channelorder');
            if ($helper->isChannelOrder()) {
                //external order cannot be reordered
                $controller->getRequest()->setParam('order_id', null);
                /** @var $session Mage_Adminhtml_Model_Session_Quote */
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError(
                    $helper->__('Order #%s cannot be reordered because it is from external marketplace.',
                        $order->getData('increment_id')));
            }
        }
    }
}
