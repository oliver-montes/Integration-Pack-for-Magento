<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Customer new password field renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Xcom_Ebay_Block_Adminhtml_Renderer_Publish_Price extends Varien_Data_Form_Element_Abstract
{
    /**
     * Get Html output.
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getElementHtmlType();
        $html .= $this->getElementHtmlValueType();
        $html .= $this->getElementHtmlValue();
        return $html;
    }

    public function getElementHtmlValue()
    {
        $html = '<input id="'.$this->getHtmlId().'_value" name="'.$this->getName().'_value" '
            .'value="'.$this->getEscapedValue('value').'" '
            .'class="' . ($this->getRequired() ? 'required-entry' : '') .' input-text price '
            . ($this->getRequired() ?
                    'validate-greater-than-zero validate-two-digit-fraction validate-less-than-hundred-custom" ' : '"')
            .'style="width:50px;position:relative;left:-48px;"/>'."\n";
        $html.= $this->getAfterElementHtml();
        return $html;
    }

    public function getElementHtmlValueType()
    {
        $this->addClass('select');
        $html = '<select id="'.$this->getHtmlId().'_value_type" name="'.$this->getName().'_value_type" '
            .'style="width:50px;position:relative;left:68px;">'."\n";

        $value = $this->getEscapedValue('value_type');
        if (!is_array($value)) {
            $value = array($value);
        }

        $values = array(
            array(
                'label' => '%',
                'value' => 'percent',
            ),
            array(
                'label' => '$',
                'value' => 'abs',
            ),
        );
        foreach ($values as $option) {
            $html .= $this->_optionToHtml($option, $value);
        }

        $html.= '</select>'."\n";
        return $html;
    }

    public function getElementHtmlType()
    {
        $helper = Mage::helper('xcom_ebay');

        $this->addClass('select');
        $html = '<select id="'.$this->getHtmlId().'_type" name="'.$this->getName().'_type" '
            .'style="width:180px;" ' . ($this->getRequired() ? 'class="required-entry"' : '') . '>'."\n";

        $value = $this->getEscapedValue('type');
        if (!is_array($value)) {
            $value = array($value);
        }

        $values = array(
            array(
                'label' => '',
                'value' => '',
            ),
            array(
                'label' => $helper->__('Magento Price'),
                'value' => 'magentoprice',
            ),
            array(
                'label' => $helper->__('Markup'),
                'value' => 'markup',
            ),
            array(
                'label' => $helper->__('Discount'),
                'value' => 'discount',
            ),
        );
        foreach ($values as $option) {
            $html .= $this->_optionToHtml($option, $value);
        }

        $html.= '</select>'."\n";
        return $html;
    }

    protected function _optionToHtml($option, $selected)
    {
        $html = '<option value="'.$this->_escape($option['value']).'"';
        if (in_array($option['value'], $selected)) {
            $html.= ' selected="selected"';
        }
        $html.= '>'.$this->_escape($option['label']). '</option>'."\n";
        return $html;
    }
}
