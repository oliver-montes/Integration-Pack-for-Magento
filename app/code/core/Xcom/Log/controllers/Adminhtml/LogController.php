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
 * @package     Xcom_Log
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Log_Adminhtml_LogController extends Mage_Adminhtml_Controller_Action
{

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('X.commerce Synchronization Log'));
        $this->loadLayout();
        $this->_setActiveMenu('system/xfabric');
        $this->_addContent($this->getLayout()->createBlock('xcom_log/adminhtml_log'));
        $this->renderLayout();
    }

    /**
     * Clear all logs
     *
     * @return void
     */
    public function clearAction()
    {
        try {
            Mage::getResourceModel('xcom_log/log')->clearAll();
            $this->_getSession()->addSuccess($this->__('All logs have been removed.'));
        }
        catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}
