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
require_once 'Mage/Adminhtml/controllers/Sales/Order/CreditmemoController.php';

class Xcom_ChannelOrder_Adminhtml_Sales_Order_CreditmemoController
    extends Mage_Adminhtml_Sales_Order_CreditmemoController
{
    /**
     * Overwritten parent action.
     * In case if order is related to channel, channelView action is used.
     *
     * @return void
     */
    public function newAction()
    {
        $creditmemo = $this->_initCreditmemo();
        if ($creditmemo) {
            if (Mage::helper('xcom_channelorder')->isChannelOrder()) {
                $this->_forward('xcomNew');
                return;
            } else {
                $this->_viewNewLayout();
            }
        } else {
            $this->_forward('noRoute');
        }
    }

    /**
     * Customized Channel Order New action
     *
     * @return void
     */
    public function xcomNewAction()
    {
        $this->_viewNewLayout();
    }

    /**
     * Generate layout for Channel Order New action
     *
     * @return void
     */
    protected function _viewNewLayout()
    {
        $creditmemo = Mage::registry('current_creditmemo');
        if ($creditmemo->getInvoice()) {
            $this->_title($this->__("New Memo for #%s", $creditmemo->getInvoice()->getIncrementId()));
        } else {
            $this->_title($this->__("New Memo"));
        }

        if ($comment = Mage::getSingleton('adminhtml/session')->getCommentText(true)) {
            $creditmemo->setCommentText($comment);
        }
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->renderLayout();
    }

    /**
     * Action for saving credit memo, checkbox must be always disabled
     * Redirect to parent saveAction method
     */
    public function memosaveAction()
    {
        if ($this->getRequest()->getParam('send_email')) {
            $this->getRequest()->setParam('send_email', 0);
        }
        $this->_forward('save');
    }

    /**
     * creditmemo information page
     */
    public function viewAction()
    {
        $creditmemo = $this->_initCreditmemo();
        if ($creditmemo) {
            if (Mage::helper('xcom_channelorder')->isChannelOrder()) {
                $this->_forward('xcomview');
                return;
            } else {
                $this->_viewNewLayout();
            }
        } else {
            $this->_forward('noRoute');
        }
    }

    /**
     * Customized Channel Order View action
     *
     * @return void
     */
    public function xcomviewAction()
    {
        $this->_viewViewLayout();
    }

    /**
     * Generate layout for Channel Order View action
     *
     * @return void
     */
    protected function _viewViewLayout()
    {
        $creditmemo = Mage::registry('current_creditmemo');
        if ($creditmemo->getInvoice()) {
            $this->_title($this->__("View Memo for #%s", $creditmemo->getInvoice()->getIncrementId()));
        } else {
            $this->_title($this->__("View Memo"));
        }

        $this->loadLayout();
        $v = $this->getLayout()->getUpdate()->getHandles();

        $this->getLayout()->getBlock('sales_creditmemo_view')
            ->updateBackButtonUrl($this->getRequest()->getParam('come_from'));
        $this->_setActiveMenu('sales/order')
            ->renderLayout();
    }
}
