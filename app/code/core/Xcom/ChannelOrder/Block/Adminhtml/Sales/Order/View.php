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
class Xcom_ChannelOrder_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{
    /**
     * Check possible render buttons
     */
    public function __construct()
    {
        parent::__construct();
        if ($this->helper('xcom_channelorder')->isChannelOrder()) {
            $this->_disableButton('order_edit');
            $this->_disableButton('order_reorder');
            $this->_disableButton('order_invoice');
            $this->_disableButton('send_notification');
        }
        $message = $this->jsQuoteEscape(
            $this->__('This will Hold Order in Magento. This request is not sent to channel.')
        );
        $this->updateButton('order_hold', 'onclick',
            "confirmSetLocation('{$message}', '{$this->getHoldUrl()}')");
    }

    /**
     * @param string $id
     * @return Xcom_ChannelOrder_Block_Adminhtml_Sales_Order_View
     */
    protected function _disableButton($id)
    {
        $this->updateButton($id, 'class', 'disabled');
        $this->updateButton($id, 'disabled', true);
        $this->updateButton($id, 'onclick', '');
        return $this;
    }
}
