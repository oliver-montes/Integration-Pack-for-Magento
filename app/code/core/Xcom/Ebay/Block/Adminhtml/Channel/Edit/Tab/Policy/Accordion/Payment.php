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

class Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Accordion_Payment extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('xcom/ebay/channel/tab/policy/payment.phtml');
    }

    protected function _prepareForm()
    {
        $form       = new Varien_Data_Form();
        $fieldset   = $form->addFieldset('payment_fieldset', array());
        $fieldset->addField('payment_methods', 'checkboxes', array(
            'name'      => 'payment_name[]',
            'values'    => $this->_getPaymentMethodOptionArray(),
            'label'     => $this->__('Payment Methods'),
            'title'     => $this->__('Payment Methods'),
            'required'  => true,
            'checked'   => $this->getPolicy() ? $this->getPolicy()->getPaymentName() : array()
        ));

        $fieldset->addField('currency', 'select', array(
            'name'      => 'currency',
            'label'     => $this->__('Currency'),
            'title'     => $this->__('Currency'),
            'required'  => true,
            'values'    => $this->_getCurrencyOptionArray(),
            'value'     => $this->getPolicy() ? $this->getPolicy()->getCurrency() : '',
        ));

        $fieldset->addField('apply_tax', 'checkbox', array(
            'name'      => 'apply_tax',
            'label'     => $this->__('Apply Tax Rule'),
            'title'     => $this->__('Apply Tax Rule'),
            'value'     => '1',
            'checked'   => $this->getPolicy() ? $this->getPolicy()->getApplyTax() : false
        ));
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return string
     */
    public function getCheckboxFieldId()
    {
        return 'payment_methods_PAYPAL';
    }

    /**
     * @return Xcom_Mmp_Model_Channel
     */
    protected function _getChannel()
    {
        return Mage::registry('current_channel');
    }

    /**
     * @return Xcom_Mmp_Model_Policy
     */
    public function getPolicy()
    {
        return Mage::registry('current_policy');
    }

    /**
     * @return array
     */
    protected function _getCurrencyOptionArray()
    {
        $currencies = Mage::getResourceModel('xcom_mmp/currency')->getCurrencies($this->_getChannel());
        return $this->_getOptionArray($currencies);
    }

    /**
     * @return array
     */
    protected function _getPaymentMethodOptionArray()
    {
        $paymentMethods = Mage::getResourceModel('xcom_mmp/paymentMethod')->getPaymentMethods($this->_getChannel());
        return $this->_getOptionArray($paymentMethods);
    }

    /**
     * @param array $values
     * @return array
     */
    protected function _getOptionArray(array $values)
    {
        $options = array();
        foreach ($values as $code) {
            $options[] = array('value' => $code, 'label' => $code);
        }
        return $options;
    }
}
