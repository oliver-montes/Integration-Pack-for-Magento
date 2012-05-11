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
 * @package     Xcom_Ebay
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Accordion extends Mage_Adminhtml_Block_Widget_Accordion
{
    protected function _prepareLayout()
    {
        $this->setId('channelPolicyAccordion');

        $this->addItem('general', array(
            'title'     => $this->__('General'),
            'content'   => $this->_getBlockHtml('xcom_ebay/adminhtml_channel_edit_tab_policy_accordion_general')
        ));

        $this->addItem('payment', array(
            'title'     => $this->__('Payment'),
            'content'   => $this->_getBlockHtml('xcom_ebay/adminhtml_channel_edit_tab_policy_accordion_payment')
        ));

        $this->addItem('shipping', array(
            'title'     => $this->__('Shipping'),
            'content'   => $this->_getBlockHtml('xcom_ebay/adminhtml_channel_edit_tab_policy_accordion_shipping')
        ));

        $this->addItem('return', array(
            'title'     => $this->__('Return'),
            'content'   => $this->_getBlockHtml('xcom_ebay/adminhtml_channel_edit_tab_policy_accordion_return')
        ));
    }

    protected function _getBlockHtml($name)
    {
        return $this->getLayout()->createBlock($name)->toHtml();
    }
}
