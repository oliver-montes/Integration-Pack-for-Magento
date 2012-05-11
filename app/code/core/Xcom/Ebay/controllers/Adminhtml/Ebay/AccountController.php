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

class Xcom_Ebay_Adminhtml_Ebay_AccountController extends Mage_Adminhtml_Controller_Action
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

        /** @var $account Xcom_Mmp_Model_Account */
        $account = Mage::getModel('xcom_mmp/account');

        $accountId = (int)$this->getRequest()->getParam($idFieldName);
        if ($accountId) {
            $account->load($accountId);
        } else {
            $environment = (int)$this->getRequest()->getParam('environment');
            $userId = $this->getRequest()->getParam('user_id');
            if ($environment && $userId) {
                $account->loadAccount($environment, $userId);
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
                if (Mage::getResourceModel('xcom_mmp/channel')->validateChannelsByAccountId($accountId)) {
                    $message = Mage::helper('xcom_mmp')->__('You have one or more Channel(s) associated with this ' .
                                                             'Account. Your associated channel, Policies and ' .
                                                             'Listings will be deleted.');
                    Mage::throwException($message);
                }
                $model = Mage::getModel('xcom_mmp/account')->load($accountId);
                $model->delete();
                Mage::getSingleton('adminhtml/session')
                        ->addSuccess(Mage::helper('xcom_mmp')->__('The account has been deleted.'));
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
     * Authorization init action.
     * Send InitAuthorizationMessage.
     *
     * @return void
     */
    public function initAction()
    {
        /** @var $account Xcom_Ebay_Model_Account */
        $account = $this->_initAccount();

        if (!$account->getId()) {
            $environmentId = (int)$this->getRequest()->getParam('environment');
        } else {
            $environmentId = $account->getEnvironment();
        }
        $environment = $this->_initEnvironment($environmentId);

        $helper = Mage::helper('core');
        try {
            $options = array(
                'environment_name' => strtolower($environment->getEnvironment()),
                'guid' => Mage::helper('core')->uniqHash(),
                'user_marketplace_id' => $account->getUserId()
            );

            $responseObject = Mage::helper('xcom_xfabric')
                ->send('marketplace/authorization/init', $options);
            $responseData = $this->_prepareInitAuthorizationMessageResponse($responseObject);

            if (empty($responseData)) {
                $this->getResponse()->setBody($helper->jsonEncode(array(
                    'error' => true,
                    'message' => $this->__('Authorization failed. Please try again.')
                )));
            } elseif (isset($responseData['error'])) {
                $this->getResponse()->setBody($helper->jsonEncode(array(
                    'error' => true,
                    'message' => $responseData['error']
                )));
            } else {
                $this->getResponse()->setBody($helper->jsonEncode($responseData));
            }
        } catch (Xcom_Xfabric_Exception $e) {
            $this->getResponse()->setBody($helper->jsonEncode(array(
                'error' => true,
                'message' => $e->getMessage()
            )));
        } catch (Exception $e) {
            Mage::logException($e);
            $this->getResponse()->setBody($helper->jsonEncode(array(
                'error' => true,
                'message' => $e->getMessage()
            )));
        }
    }

    /**
     * Returns environment instance.
     *
     * @param int $environmentId
     * @return Mage_Core_Model_Abstract
     */
    protected function _initEnvironment($environmentId)
    {
        $environment = Mage::getModel('xcom_mmp/environment');
        if ($environmentId) {
            $environment = $environment->load((int)$environmentId);
        }
        return $environment;
    }

    /**
     * Prepare response from InitAuthorizationMessage.
     *
     * @param $object
     * @return array|string
     */
    protected function _prepareInitAuthorizationMessageResponse($object)
    {
        if (!is_object($object)) {
            return '';
        }
        $result = array();
        $data = $object->getResponseData();
        if (!empty($data['errors'])) {
            $errors = array();
            foreach ($data['errors'] as $error) {
                $errors[] = sprintf("Error (%s) - %s", $error['code'], $error['message']);
            }
            $result['error'] = implode("\n", $errors);
        } else {
            if (!empty($data['redirectURL'])) {
                $result['redirectURL'] = $data['redirectURL'];
            }
            if (!empty($data['authId'])) {
                $result['authId'] = $data['authId'];
            }
        }
        return count($result) ? $result : '';
    }


    /**
     * Authorization complete action.
     * 1. Send CompleteAuthorizationMessage
     * 2. Validate Account
     * 3. Save Account
     *
     * @return void
     */
    public function completeAction()
    {
        $authId = $this->getRequest()->getParam('authId', null);
        $environment = $this->getRequest()->getParam('environment', null);
        $helper = Mage::helper('core');
        try {
            $options = array(
                'auth_id' => $authId
            );
            $responseObject = Mage::helper('xcom_xfabric')
                ->send('marketplace/authorization/userCompleted', $options);

            $responseData = $this->_prepareCompleteAuthorizationMessageResponse($responseObject);

            if (empty($responseData) || empty($responseData['xAccountId'])) {
                $this->getResponse()->setBody($helper->jsonEncode(array(
                    'error' => true,
                    'message' => $this->__('Authorization failed. Please try again.')
                )));
            } elseif (isset($responseData['error'])) {
                $this->getResponse()->setBody($helper->jsonEncode(array(
                    'error' => true,
                    'message' => $responseData['error']
                )));
            } else {
                $data = array(
                    'auth_id'       => $authId,
                    'user_id'       => !empty($responseData['userMarketplaceId'])
                            ? $responseData['userMarketplaceId'] : null,
                    'xaccount_id'   => $responseData['xAccountId'],
                    'validated_at'  => !empty($responseData['validatedAt'])
                            ? $responseData['validatedAt'] : null,
                    'environment'   => $environment,
                );
                $this->getRequest()->setParam('user_id', $responseData['userMarketplaceId']);
                $account  = $this->_initAccount();
                $account->addData($data);
                if (!$account->getId()) {
                    $account->setData('channeltype_code', Mage::helper('xcom_ebay')->getChanneltypeCode());
                }
                $account->validate();
                $account->save();
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                $this->getResponse()->setBody($helper->jsonEncode($responseData));
            }
        } catch (Xcom_Xfabric_Exception $e) {
            $this->getResponse()->setBody($helper->jsonEncode(array(
                    'error' => true,
                    'message' => $e->getMessage()
            )));
        } catch (Exception $e) {
            Mage::logException($e);
            $this->getResponse()->setBody($helper->jsonEncode(array(
                    'error' => true,
                    'message' => $e->getMessage()
            )));
        }
    }

    /**
     * Prepare response from CompleteAuthorizationMessage.
     *
     * @param $object
     * @return array|string
     */
    protected function _prepareCompleteAuthorizationMessageResponse($object)
    {
        if (!is_object($object)) {
            return '';
        }
        $result = array();
        $data = $object->getResponseData();

        if (!empty($data['errors'])) {
            $errors = array();
            foreach ($data['errors'] as $error) {
                $errors[] = $error['message'];
            }
            $result['error'] = implode("\n", $errors);
        } else {
            if (!empty($data['xAccountId'])) {
                $result['xAccountId'] = $data['xAccountId'];
            }

            if (!empty($data['userMarketplaceId'])) {
                $result['userMarketplaceId'] = $data['userMarketplaceId'];
                if (!empty($data['authorizationExpiration'])) {
                    //authorizationExpiration contains milliseconds
                    $date       = date('Y-m-d', $data['authorizationExpiration'] / 1000);
                    $result['validatedAt'] = $date;
                    $result['validatedAtText'] = $this->__('Valid Authorization Token.<br /> Valid until %s', $date);
                }
            }
        }
        return count($result) ? $result : '';
    }

    /**
     * Check if action allowed.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('channels/account')
            && Mage::helper('xcom_ebay')->isExtensionEnabled();
    }
}
