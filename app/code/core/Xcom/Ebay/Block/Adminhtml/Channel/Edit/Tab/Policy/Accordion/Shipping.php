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

class Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Accordion_Shipping extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('fieldset', array());
        $fieldset->addField('handling_time', 'select', array(
            'name'      => 'handling_time',
            'label'     => $this->__('Handling Time'),
            'values'    => $this->_getHandlingTimeOptionArray(),
            'value'     => $this->getPolicy() ? $this->getPolicy()->getHandlingTime() : '',
            'required'  => true
        ));

        $elements = array(
            array('country', 'select', array(
                    'name'     => 'location',
                    'values'   => $this->_getCountryOptionArray(),
                    'value'    => $this->getPolicy() ? $this->getPolicy()->getLocation() : '',
                    'required' => true,
                    'no_span'  => true,
                    'class'    => 'country'
                )
            ),
            array('postal_code', 'text', array(
                    'name'     => 'postal_code',
                    'value'    => $this->getPolicy() ? $this->getPolicy()->getPostalCode() : '',
                    'required' => true,
                    'no_span'  => true,
                    'class'    => 'postal_code'
                )
            )
        );

        $fieldset->addType('inline_group',
            Mage::getConfig()->getBlockClassName('xcom_ebay/adminhtml_renderer_linegroup')
        );

        $fieldset->addField('fieldgroup', 'inline_group', array(
                'label'    => $this->__('Product Location Postal Code'),
                'no_span'  => true,
                'class'    => 'fieldline',
                'required' => true,
                'elements' => $elements
            )
        );

        $fieldset->addField('shipping_method', 'text', array(
            'name'=>'shipping_method',
            'value'=>'true',
        ));

        $form->getElement('shipping_method')->setRenderer(
            $this->getLayout()->createBlock('xcom_ebay/adminhtml_channel_edit_tab_policy_accordion_shipping_method')
        );

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return Xcom_Mmp_Model_Channel
     */
    public function getChannel()
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
    protected function _getCountryOptionArray()
    {
        $options    = array();
        $countries  = Mage::getResourceModel('xcom_mmp/country')->getCountries($this->getChannel());
        foreach ($countries as $countryCode => $description) {
            $options[] = array(
                'value' => $countryCode,
                'label' => $description
            );
        }

        array_unshift($options, array('value' => '', 'label' => $this->__('Select a Country')));

        return $options;
    }

    /**
     * @return array
     */
    protected function _getHandlingTimeOptionArray()
    {
        $options = array(array('value' => '', 'label' => $this->__('Please Select One')));
        $options[] = array('value' => 'None', 'label' => $this->__('None'));
        $handlingTimes   = Mage::getResourceModel('xcom_mmp/handlingTime')
            ->getHandlingTimes($this->getChannel());
        foreach ($handlingTimes as $item) {
            $options[] = array(
                'value' => $item['max_handling_time'],
                'label' => Mage::helper('core')->escapeHtml($item['description']),
            );
        }
        return $options;
    }
}
