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
 * @package     Xcom_Mmp
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
require_once 'Xcom/ChannelGroup/controllers/Adminhtml/AccountController.php';

class Xcom_Mmp_Adminhtml_AccountController extends Xcom_ChannelGroup_Adminhtml_AccountController
{
    /**
     * Enable one or multiple accounts.
     * Preconditions:
     *  - account param should be passed
     *
     * @return void
     */
    public function massEnableAction()
    {
        $accountIds = $this->getRequest()->getParam('account');
        $this->_checkAccountIds($accountIds);
        try {
            $this->_massChangeAccountAction($accountIds, 1);
            $this->_getSession()->addSuccess(
                $this->__('Total of %d account(s) were enabled.', count($accountIds))
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError('An error occurred during updating accounts.');
        }
        $this->_redirect('*/account/');
    }

    /**
     * Disable one or multiple accounts.
     * Preconditions:
     *  - account param should be passed
     *
     * @return void
     */
    public function massDisableAction()
    {
        $accountIds = $this->getRequest()->getParam('account');
        $this->_checkAccountIds($accountIds);
        try {
            $this->_massChangeAccountAction($accountIds, 0);
            $this->_getSession()->addSuccess($this->__('Total of %d account(s) were disabled.', count($accountIds)));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError('An error occurred during updating accounts.');
        }
        $this->_redirect('*/account/');
    }

    /**
     * @return void
     */
    public function massDisableValidationAction()
    {
        if (!$this->getRequest()->isAjax()) {
            return;
        }
        $accountIds = $this->getRequest()->getParam('account');
        $this->_checkAccountIds($accountIds);
        try {
            $validated = true;
            foreach ($accountIds as $accountId) {
                if (Mage::getResourceSingleton('xcom_mmp/channel')->validateChannelsByAccountId($accountId, true)) {
                    $validated = false;
                    break;
                }
            }
            if (!$validated) {
                $message = $this->__('You have one or more Channel(s) associated with this Account.');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                    'message' => $message
                )));
            }
        } catch (Exception $e) {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                'message' => $e->getMessage()
            )));
        }
    }

    /**
     * Change account status
     *
     * @param array $accountIds
     * @param int $status
     * @return Xcom_Mmp_Adminhtml_AccountController
     */
    protected function _massChangeAccountAction(array $accountIds, $status)
    {
        foreach($accountIds as $id) {
            $model = Mage::getModel('xcom_mmp/account')->load((int)$id);
            if ($status != $model->getStatus()) {
                $model->setStatus($status);
                $model->save();
            }
        }
        return $this;
    }

    /**
     * @param mixed $accountIds
     * @return Xcom_Mmp_Adminhtml_AccountController
     */
    protected function _checkAccountIds($accountIds)
    {
        if (is_array($accountIds)) {
            return $this;
        }
        $message = $this->__('Please select account(s).');
        if ($this->getRequest()->isAjax()) {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                'message' => $message
            )));
        } else {
            $this->_getSession()->addError($message);
        }
        $this->_redirect('*/account/');
        return $this;
    }

    /**
     * Check if action allowed.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('channels/account');
    }
}
