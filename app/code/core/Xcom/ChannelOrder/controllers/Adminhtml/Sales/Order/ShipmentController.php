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
require_once 'Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php';

class Xcom_ChannelOrder_Adminhtml_Sales_Order_ShipmentController
    extends Mage_Adminhtml_Sales_Order_ShipmentController
{
    /**
     * Overwritten parent action.
     * In case if order is related to channel, channelNew action is used.
     *
     * @return void
     */
    public function newAction()
    {
        if ($shipment = $this->_initShipment()) {
            $this->_initOrder($shipment);
            $this->_title($this->__('New Shipment'));

            if ($comment = Mage::getSingleton('adminhtml/session')->getCommentText(true)) {
                $shipment->setCommentText($comment);
            }

            if (Mage::helper('xcom_channelorder')->isChannelOrder()) {
                $this->_forward('channelNew');
                return;
            }

            $this->_renderLayout();
        } else {
            $this->_redirect('*/sales_order/view', array('order_id'=>$this->getRequest()->getParam('order_id')));
        }
    }

    /**
     * init order instance
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     */
    protected function _initOrder(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $order = Mage::getModel('sales/order')->load($shipment->getOrderId());
        Mage::register('current_order', $order);
    }

    /**
     * Overwritten parent action.
     * In case if order is related to channel, channelView action is used.
     *
     *  @return void
     */
    public function viewAction()
    {
        if ($shipment = $this->_initShipment()) {
            $this->_initOrder($shipment);
            $this->_title($this->__('View Shipment'));

            if (Mage::helper('xcom_channelorder')->isChannelOrder()) {
                $this->_forward('channelView');
                return;
            }
            $this->loadLayout();
            $this->getLayout()->getBlock('sales_shipment_view')
                ->updateBackButtonUrl($this->getRequest()->getParam('come_from'));
            $this->_setActiveMenu('sales/order')
                ->renderLayout();
        } else {
            $this->_forward('noRoute');
        }
    }

    public function channelViewAction()
    {
        $this->_checkAndRenderLayout();
    }

    public function channelNewAction()
    {
        $this->_checkAndRenderLayout();
    }

    protected function _checkAndRenderLayout()
    {
        if (Mage::registry('current_shipment')) {
            $this->_renderLayout();
        } else {
            $this->_forward('noRoute');
        }
    }

    /**
     * Removes notified and visible on frontend params.
     *
     * @return void
     */
    public function addCommentCancelAction()
    {
        $data = $this->getRequest()->getPost('comment');
        unset($data['is_customer_notified'], $data['is_visible_on_front']);
        $this->getRequest()->setPost('comment', $data);
        $this->_forward('addComment');
    }

    /**
     * Load and render layout.
     * Set active menu.
     *
     * @return void
     */
    protected function _renderLayout()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->renderLayout();
    }
}

