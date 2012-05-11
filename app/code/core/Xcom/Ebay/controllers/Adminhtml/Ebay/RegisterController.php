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

class Xcom_Ebay_Adminhtml_Ebay_RegisterController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
        try {
            Mage::getModel('core/config')->saveConfig(
                Xcom_Ebay_Helper_Data::XML_PATH_XCOM_CHANNEL_REGISTRATION_EXTENSION_ENABLED, true);
            $this->_getSession()->addSuccess($this->__('Registration succeeded.'));
            // clear cache
            Mage::app()->cleanCache(array(Mage_Core_Model_Config::CACHE_TAG));
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Registration failed. Reason: %s', $e->getMessage()));
        }
    }
}
