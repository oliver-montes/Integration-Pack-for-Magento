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
class Xcom_Ebay_Block_Adminhtml_System_Form_Renderer_Config_OrderSyncStartManual
    extends Mage_Adminhtml_Block_System_Config_Form_Field implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Get element HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $ajaxPath = '<script type="text/javascript" src="' .
            $this->getJsUrl('xcom/adminhtml/system/orderSync.js') .
            '"></script>';
        $ajaxUrl = '<script type="text/javascript">' .
            'var channel_order_ajax_responce = "' .
            Mage::getUrl('adminhtml/ebay_order_sync/ajax') . '";' .
            '</script>';
        return
        '<button class="scalable add" type="button" id="order_sync">
          <span>' . $this->__('Manual: Pull Orders from eBay') . '</span>
        </button>' . $ajaxUrl . $ajaxPath;
    }
}
