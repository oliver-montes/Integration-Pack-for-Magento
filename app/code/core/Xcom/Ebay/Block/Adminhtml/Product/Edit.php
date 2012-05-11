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
class Xcom_Ebay_Block_Adminhtml_Product_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'xcom_ebay';
        $this->_controller = 'adminhtml_product';

        switch ($this->getRequest()->getActionName()) {
            case 'publish':
                $this->_updateButton('save', 'label', $this->__('Publish'));
                break;

            case 'history':
                $this->_removeButton('save');
                $this->_removeButton('reset');
                break;

            case 'listingerror':
                $this->_removeButton('save');
                $this->_removeButton('reset');
                break;
        }

        $this->_removeButton('delete');
    }

    /**
     * Prepare page header text.
     *
     * @return string
     */
    public function getHeaderText()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'publish':
                return $this->__('Publish Settings');
                break;

            case 'history':
                return $this->__('Channel Listing Status Details');
                break;

            case 'listingerror':
                return $this->__('Listing Error Details');
                break;
        }
        return '';
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        $request = $this->getRequest();
        switch ($request->getActionName()) {
            case 'listingerror':
                $url = $this->getUrl('*/ebay_product/history/', array(
                    'id'      => $request->getParam('id'),
                    'channel' => $request->getParam('channel')));
                break;
            default:
                $url = $this->getUrl('*/channel_product/', array(
                    'type'    => 'ebay',
                    'store'   => $request->getParam('store')));
                break;
        }
        return $url;
    }

     /**
     * Prepares form scripts
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $validationMessage = addcslashes(Mage::helper('xcom_ebay')->__('Please enter valid number. Use no more than two digits for fraction'), "\\'\n\r");
        $validationMessageHundred = addcslashes(Mage::helper('xcom_ebay')->__('Please enter less than 100'), "\\'\n\r");
        $this->_formScripts[] = "
        Validation.addAllThese([
            ['validate-two-digit-fraction', '$validationMessage', function (v) {
               return /^(\d+|\d+\.\d{1,2})$/.test(v);
            }]
        ]);
        Validation.addAllThese([
            ['validate-less-than-hundred-custom', '$validationMessageHundred', function (v) {
                if ($('price_type').value == 'discount' && $('price_value_type').value == 'percent') {
                    return parseFloat(v) < 100;
                }
                return true;
            }]
        ]);
        ";
        return parent::_prepareLayout();
    }
}
