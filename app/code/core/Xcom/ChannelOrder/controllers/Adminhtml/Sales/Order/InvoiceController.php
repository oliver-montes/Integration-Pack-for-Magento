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
require_once 'Mage/Adminhtml/controllers/Sales/Order/InvoiceController.php';

class Xcom_ChannelOrder_Adminhtml_Sales_Order_InvoiceController extends Mage_Adminhtml_Sales_Order_InvoiceController
{
    /**
     * Overwritten parent action.
     * In case if order is related to channel, channelView action is used.
     */
    public function viewAction()
    {
        $invoice = $this->_initInvoice();
        if ($invoice) {
            if (Mage::helper('xcom_channelorder')->isChannelOrder()) {
                $this->_forward('channelView');
                return;
            } else {
                $this->_viewNewLayout();
            }
        }
        else {
            $this->_forward('noRoute');
        }
    }

    /**
     * Channel Order View action
     */
    public function channelViewAction()
    {
        $this->_viewNewLayout();
    }

    /**
     * View layout
     */
    protected function _viewNewLayout()
    {
        $this->_title(sprintf("#%s", Mage::registry('current_invoice')->getIncrementId()));
        $this->loadLayout()
            ->_setActiveMenu('sales/order');
        $this->getLayout()->getBlock('sales_invoice_view')
            ->updateBackButtonUrl($this->getRequest()->getParam('come_from'));
        $this->renderLayout();
    }
}
