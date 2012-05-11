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

require_once 'Xcom/Mmp/controllers/Adminhtml/ChannelController.php';

class Xcom_Ebay_Adminhtml_Ebay_ChannelController extends Xcom_Mmp_Adminhtml_ChannelController
{
    /**
     * Array of actions which can be processed without secret key validation
     * @var array
     */
    protected $_publicActions = array('policy', 'savePolicy');

    /**
     * Initialize general settings for channel
     *
     * @return Xcom_Ebay_Model_Channel
     */
    protected function _initChannel()
    {
        $this->_title($this->__('Channel'))
             ->_title($this->__('eBay'))
             ->_title($this->__('Manage Channel'));

        $channel            = Mage::getModel('xcom_ebay/channel');
        $channelId          = $this->getRequest()->getParam('channel_id');
        $channelSiteCode    = $this->getRequest()->getParam('site_code');
        $channelAccountId   = $this->getRequest()->getParam('account_id');

        if ($channelId) {
            $channel->load($channelId);
        } elseif ($channelSiteCode) {
            $channel->setSiteCode($channelSiteCode);
            $channel->setAccountId($channelAccountId);
        }

        Mage::register('current_channel', $channel);
        return $channel;
    }

    /**
     * Create new channel action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit channel action
     *
     * @return void
     */
    public function editAction()
    {
        $data = $this->_getSession()->getXcomEbayData();
        if ($data) {
            $siteCode  = !empty($data['site_code'])  ? $data['site_code'] : null;
            $accountId  = !empty($data['account_id']) ? $data['account_id'] : null;

            $this->getRequest()->setParam('site_code', $siteCode);
            $this->getRequest()->setParam('account_id', $accountId);
        }

        $channel  = $this->_initChannel();
        if ((int) $this->getRequest()->getParam('channel_id') && !$channel->getId()) {
            $this->_getSession()->addError($this->__('This channel no longer exists.'));
            $this->_redirectToGrid();
            return;
        }

        try {
            $this->loadLayout()
                ->_setActiveMenu('channels/manage');
            $this->renderLayout();
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Problem with generation channel page:'));
            $this->_getSession()->addError($e->getMessage());
            $this->_redirectToGrid();
        }
    }

    /**
     * Edit channel action
     *
     * @return void
     */
    public function saveAction()
    {
        $redirectBack = $this->getRequest()->getParam('back', false);
        $data = $this->getRequest()->getPost();
        $channel = $this->_initChannel();
        if ($data) {
            $channel->addData($data);
            try {
                //do not allow to save channel if it was not authorized
                if (!$channel->getAccount()->getUserId()) {
                    Mage::throwException(
                        $this->__('Unable to the complete request. Please refer to the User Guide to verify your settings and try again. If the error persists, contact your administrator.')
                    );
                }
                $channel->validate();
                $channel->save();
                $this->_getSession()->addSuccess($this->__('The Channel has been saved.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->setXcomEbayData($channel->getData());
                $this->_getSession()->addError($e->getMessage())
                    ->setChannelData($data);
                $redirectBack = true;
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->setXcomEbayData($channel->getData());
                $this->_getSession()->addError($this->__('An error occurred while saving the Channel.'));
                $redirectBack = true;
            }
        }

        if ($redirectBack) {
            $this->_redirect('*/*/edit', array(
                'channel_id'        => $channel->getId(),
                '_current'  => true
            ));
        } else {
            $this->_redirectToGrid();
        }
    }

    /**
     * Delete channel action
     *
     * @return void
     */
    public function deleteAction()
    {
        $channelId = $this->getRequest()->getParam('channel_id');
        if ($channelId) {
            $channel = Mage::getModel('xcom_ebay/channel')->load((int)$channelId);
            try {
                $channel->delete();
                $this->_getSession()->addSuccess($this->__('The channel has been deleted.'));
            }
            catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirectToGrid();
    }

    /**
     * Set redirect into grid page
     *
     * @param array $params
     */
    protected function _redirectToGrid(array $params = array())
    {
        $params = array_merge(array('type' => Mage::helper('xcom_ebay')->getChanneltypeCode()), $params);
        $this->_redirect('*/channel/', $params);
    }

    /**
     * Show "Policy" tab.
     */
    public function policyAction()
    {
        $this->_initChannel();
        if (!$this->getRequest()->getParam('channel_id')) {
            $this->_getSession()->addError($this->__('Channel should be specified.')); //TODO correct
        }
        if ($data = $this->_getSession()->getXcomEbayPolicyData()) {
            $policy = Mage::getModel('xcom_ebay/policy')->addData($data);
            $policy->setEditFlag(true);
            $this->_getSession()->unsXcomEbayPolicyData();
        } else {
            $policy = $this->_initPolicy();
            if ($this->getRequest()->getParam('edit')) {
                $policy->setEditFlag(true);
            }
        }
        Mage::register('current_policy', $policy);
        $this->loadLayout(false)
            ->renderLayout();
    }

    /**
     * Save Policy.
     */
    public function savePolicyAction()
    {
        $channelId = (int)$this->getRequest()->getParam('channel_id');
        $channel = Mage::getModel('xcom_ebay/channel')->load($channelId);
        if (!$channel->getId()) {
            $this->_getSession()
                ->addError('The Policy can not be saved. Channel does not exist.');
            $this->_redirectToGrid();
            return;
        }
        if ($data = $this->getRequest()->getPost()) {
            /** @var $policy Xcom_Ebay_Model_Policy */
            $policy = $this->_initPolicy();
            $policy->addData($data);
            try {
                $policy->prepareShippingData();
                $policy->validate();
                $options = array(
                    'policy'  => $policy,
                    'channel' => $channel
                );
                if ($policy->getId()) {
                    Mage::helper('xcom_xfabric')->send('marketplace/profile/update', $options);
                } else {
                    Mage::helper('xcom_xfabric')->send('marketplace/profile/create', $options);
                }
                $policy->save();
                $policy->savePolicyShipping();
                $this->_getSession()->addSuccess($this->__('The Policy has been saved.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->setXcomEbayPolicyData($policy->getData());
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->setXcomEbayPolicyData($policy->getData());
                $this->_getSession()->addError($this->__('An error occurred while saving the Policy.'));
            }
        }
        $this->getRequest()->setParam('policy_id', 0);
        $this->_forward('policy');
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _initPolicy()
    {
        $policy     = Mage::getModel('xcom_ebay/policy');
        $policyId   = $this->getRequest()->getParam('policy_id');
        if (!empty($policyId)) {
            $policy->load((int)$policyId);
        }
        return $policy;
    }

    /**
     * @return void
     */
    public function massEnablePolicyAction()
    {
        try {
            $countProcessed = $this->_massChangePolicyAction(1);
            if ($countProcessed) {
                $this->_getSession()->addSuccess($this->__('Total of %d policy(s) were enabled', $countProcessed));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_forward('policy');
    }

    /**
     * @return void
     */
    public function massDisablePolicyAction()
    {
        try {
            $countProcessed = $this->_massChangePolicyAction(0);
            if ($countProcessed) {
                $this->_getSession()->addSuccess($this->__('Total of %d policy(s) were disabled', $countProcessed));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_forward('policy');
    }

    /**
     * @param int $isActive
     * @return int
     */
    protected function _massChangePolicyAction($isActive)
    {
        $countProcessed = 0;
        foreach($this->getRequest()->getParam('selected_policy', array()) as $policyId) {
            $policy = Mage::getModel('xcom_ebay/policy')->load($policyId);
            $policy->setIsActive($isActive);
            $policy->save();
            ++$countProcessed;
        }
        return $countProcessed;
    }

    /**
     * Check if action allowed.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed() && Mage::helper('xcom_ebay')->isExtensionEnabled();
    }
}
