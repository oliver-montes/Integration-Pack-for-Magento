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
class Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Accordion_Shipping_Method
    extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * @var array
     */
    protected $_optionArrayHash;

    /**
     * @var array
     */
    protected $_shippingServiceHash;

    /**
     * @var array
     */
    protected $_htmlSelectCache;

    const FREE_SHIPPING = 'FREE_SHIPPING';

    protected function _construct()
    {
        parent::_construct();
        $this->setId("shipping_method");
        $this->setTemplate('xcom/ebay/channel/tab/policy/shipping/method.phtml');
    }

    /**
     * @return array
     */
    public function getShippingMethodCollection()
    {
        $shippingServices = $this->getShippingServiceArray();
        foreach ($shippingServices as &$item) {
            $item['show_shipping_cost'] = $this->showShippingCost($item['service_name']);
        }
        return $shippingServices;
    }

    /**
     * @param string $serviceName
     * @return bool
     */
    public function showShippingCost($serviceName)
    {
        return ($this->_getUnifiedValue($serviceName) != self::FREE_SHIPPING);
    }

    /**
     * @return string
     */
    public function getShippingMethodJson()
    {
        $shippingServices = $this->getShippingServiceArray();
        $jsonArray = array();

        foreach ($shippingServices as &$item) {
            $item['cost'] = $this->getShippingCost($item['shipping_id']);
            $item['show_shipping_cost'] = $this->showShippingCost($item['service_name']);
            $jsonArray[$item['shipping_id']] = $item;
        }

        return Zend_Json::encode($jsonArray);
    }

    /**
     * @return array
     */
    public function getHtmlSelect()
    {
        if ($this->_htmlSelectCache === null) {
            $this->_htmlSelectCache = $this->getLayout()->createBlock('adminhtml/html_select')
                ->setData(array(
                    'id' => 'policy_shipping_{{index}}',
                    'class' => 'select validate-select',
                    'name' => 'shipping_name[]'
                ))->setOptions($this->getShippingOptions())
                ->toHtml();
        }

        return $this->_htmlSelectCache;
    }

    /**
     * @return array
     */
    public function getShippingOptions()
    {
        if ($this->_optionArrayHash === null) {
            $optionArray = array('' => $this->__('Please select shipping method'));
            foreach ($this->getShippingMethodCollection() as $item) {
                $optionArray[$item['shipping_id']] = $item['description'];
            }
            $this->_optionArrayHash = $optionArray;
        }

        return $this->_optionArrayHash;
    }

    /**
     * Prepare global layout
     * Add "Add tier" button to layout
     *
     * @return Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Accordion_Shipping_Method
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            'label' => $this->__('Add Shipping'),
            'onclick' => 'return shippingMethod.addItem()',
            'class' => 'add'
        ));
        $button->setName('xcom_shipping_item_button');
        $this->setChild('add_button', $button);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve Add Tier Price Item button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * @return array
     */
    public function getShippingServiceArray()
    {
        if ($this->_shippingServiceHash === null) {
            $shippingService = Mage::getResourceModel('xcom_ebay/shippingService');
            if ($this->getPolicy() && $this->getPolicy()->getId()) {
                $this->_shippingServiceHash = $shippingService
                    ->getSortedShippingServices($this->getChannel(), $this->getPolicy());
            } else {
                $this->_shippingServiceHash = $shippingService->getShippingServices($this->getChannel());
            }
        }

        return $this->_shippingServiceHash;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function _getUnifiedValue($value = '')
    {
        $value = preg_replace('/ /', '_', trim($value));
        $value = preg_replace('/[^\w]/', '', $value);
        return strtoupper($value);
    }


    /**
     * @return Xcom_Mmp_Model_Policy
     */
    public function getPolicy()
    {
        return Mage::registry('current_policy');
    }

    /**
     * @return Xcom_Mmp_Model_Channel
     */
    public function getChannel()
    {
        return Mage::registry('current_channel');
    }

    /**
     * @return array
     */
    public function getPolicyShippingData()
    {
        if ($this->getPolicy() && is_array($this->getPolicy()->getShippingData())) {
            return $this->getPolicy()->getShippingData();
        }
        return array();
    }

    /**
     * @param int $shippingId
     * @return string
     */
    public function getShippingCost($shippingId)
    {
        foreach ($this->getPolicyShippingData() as $shipping) {
            if ($this->_isShippingIdExists($shipping, $shippingId)) {
                return round($shipping['cost'], 2);
            }
        }
        return '';
    }

    /**
     * @param int $shippingId
     * @return bool
     */
    public function showShippingServiceCheckbox($shippingId)
    {
        foreach ($this->getPolicyShippingData() as $shipping) {
            if ($this->_isShippingIdExists($shipping, $shippingId)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param int $shippingId
     * @return string
     */
    public function getShippingPriority($shippingId)
    {
        foreach ($this->getPolicyShippingData() as $shipping) {
            if ($this->_isShippingIdExists($shipping, $shippingId)) {
                return $shipping['sort_order'];
            }
        }
        return '';
    }

    /**
     * @param array $shipping
     * @param int $shippingId
     * @return bool
     */
    protected function _isShippingIdExists(array &$shipping, $shippingId)
    {
        return !empty($shipping['shipping_id']) && $shipping['shipping_id'] == $shippingId;
    }
}
