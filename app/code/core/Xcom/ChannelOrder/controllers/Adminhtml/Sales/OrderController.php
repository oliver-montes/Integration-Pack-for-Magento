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
require_once 'Mage/Adminhtml/controllers/Sales/OrderController.php';

class Xcom_ChannelOrder_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    /**
     * Overwritten parent action.
     * In case if order is related to channel, channelView action is used.
     *
     * @return void
     */
    public function viewAction()
    {
        if ($order = $this->_initOrder()) {
            if (Mage::helper('xcom_channelorder')->isChannelOrder()) {
                $this->_forward('channelView');
                return;
            }

            $this->_viewLayout();
        }
    }

    /**
     * Channel Order View action
     *
     * @return void
     */
    public function channelViewAction()
    {
        $this->_viewLayout();
    }

    /**
     *
     * @return void
     */
    protected function _viewLayout()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Orders'));
        $this->_initAction();
        $this->_title(sprintf("#%s", Mage::registry('current_order')->getRealOrderId()));
        $this->renderLayout();
    }
}
