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
 * @package     Xcom_Google
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Google_Adminhtml_Google_AccountController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialize general settings for account.
     *
     * @param string $idFieldName
     * @return Mage_Core_Model_Abstract
     */
    protected function _initAccount($idFieldName = 'id')
    {
        $this->_title($this->__('Authorization'));

        $account = Mage::getModel('xcom_google/account');

        $accountId = (int)$this->getRequest()->getParam($idFieldName);
        if ($accountId) {
            $account->load($accountId);
        }
        else {
            $userId = $this->getRequest()->getParam('user_id');
            if ($userId) {
                $account->loadAccount($userId);
            }
        }

        Mage::register('current_account', $account);
        return $account;
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $model  = $this->_initAccount();
        $this->loadLayout()
            ->_setActiveMenu('channels/account');

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->renderLayout();
    }

    /**
     * TODO: remove this action, if we won't use it.
     * @return
     */
    public function deleteAction()
    {
        if ($accountId = $this->getRequest()->getParam('id')) {
            try {
                if (Mage::getResourceModel('xcom_cse/channel')->validateChannelsByAccountId($accountId)) {
                    $message = Mage::helper('xcom_cse')->__('You have one or more Channel(s) associated with this ' .
                                                             'Account. Your associated channel, Policies and ' .
                                                             'Listings will be deleted.');
                    Mage::throwException($message);
                }
                $model = Mage::getModel('xcom_google/account')->load($accountId);
                $model->delete();
                Mage::getSingleton('adminhtml/session')
                        ->addSuccess(Mage::helper('xcom_cse')->__('The account has been deleted.'));
                $this->_redirect('*/account/');
                return;

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $accountId));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')
                ->addError(Mage::helper('cms')->__('Unable to find a account to delete.'));
        $this->_redirect('*/account/');
    }

    /**
     * Authorization save action.
     * 1. Send InitAuthorizationMessage
     * 2. Validate Account
     * 3. Save Account
     *
     * @return void
     */
    public function saveAction()
    {
        $account = $this->_initAccount();
        
        $addMode = !$account->getId();
              	
        try {
        	if (Mage::getModel('xcom_cse/account')->getCollection()
                  ->addChanneltypeCodeFilter(Mage::helper('xcom_google')->getChanneltypeCode())
                  ->getSize() != 0 && $addMode) {
               // Only allow one account at this time.  Merchant may have hit the back button or
               // hit this url directly, bypassing the disabled add account button.
               $message = Mage::helper('xcom_cse')->__('You already have one account configured.');
               Mage::throwException($message);
            }
                
            $user_id = $this->getRequest()->getParam('user_id');
            $target_location = $this->getRequest()->getParam('target_location');
            
            $data = array(
                'user_id'			=> $user_id,
            	'target_location'	=> $target_location
            );
            $account->addData($data);

            $responseData = $account->sendInitAuthorizationMessage();
            if (empty($responseData)) {
            	Mage::getSingleton('adminhtml/session')->addError('Authorization failed. Please try again.');
                if ($addMode)
                	$this->_redirect('*/*/edit');
                else
                	$this->_redirect('*/*/edit', array('id' => $accountId));
                return;
            } elseif (isset($responseData['error'])) {
            	Mage::getSingleton('adminhtml/session')->addError($responseData['error']);
                if ($addMode)
                	$this->_redirect('*/*/edit');
                else
                	$this->_redirect('*/*/edit', array('id' => $accountId));
                return;
            } else {
				$data['auth_id'] = $responseData['auth_id'];
				$account->addData($data);            	
                if ($addMode) {
                    $account->setData('channeltype_code', Mage::helper('xcom_google')->getChanneltypeCode());
                }
                $account->validate();
                $account->save();
                
	            Mage::getSingleton('adminhtml/session')
	                      ->addSuccess(Mage::helper('xcom_cse')->__('The account has been authorized.'));
	                      
	            $this->_redirect('*/account/', array('type' => Mage::helper('xcom_google')->getChanneltypeCode()));
            }
        } catch (Xcom_Xfabric_Exception $e) {
        		Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($addMode)
                	$this->_redirect('*/*/edit');
                else
                	$this->_redirect('*/*/edit', array('id' => $accountId));
                return;
        } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $accountId));
                return;
        }
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
