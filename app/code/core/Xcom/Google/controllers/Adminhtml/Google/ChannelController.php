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

require_once 'Xcom/Cse/controllers/Adminhtml/ChannelController.php';

class Xcom_Google_Adminhtml_Google_ChannelController extends Xcom_Cse_Adminhtml_ChannelController
{

    /**
     * Initialize general settings for channel
     *
     * @return Xcom_Google_Model_Channel
     */
    protected function _initChannel()
    {
        $this->_title($this->__('Channel'))
             ->_title($this->__('Google'))
             ->_title($this->__('Manage Channel'));

        $channel            = Mage::getModel('xcom_google/channel');
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
        $data = $this->_getSession()->getXcomGoogleData();
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
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $data = $this->getRequest()->getPost();
        $channel = $this->_initChannel();
        if ($data) {
            $channel->addData($data);
            try {
                //do not allow to save channel if it was not authorized
                if (!$channel->isAuthorized()) {
                    Mage::throwException($this->__('Unable to complete the request. ' .
                        'Please refer to the User Guide to verify your settings and try again. ' .
                        'If the error persists, contact your administrator.'));
                }
                $channel->validate();
                $channel->save();
                $this->_getSession()->addSuccess($this->__('The channel has been saved.'));
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->setXcomGoogleData($channel->getData());
                $this->_getSession()->addError($e->getMessage())
                    ->setChannelData($data);
                $redirectBack = true;
            }
            catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->setXcomGoogleData($channel->getData());
                $this->_getSession()->addError($this->__('An error occurred while saving the channel.'));
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
            $channel = Mage::getModel('xcom_google/channel')->load((int)$channelId);
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
    protected function _redirectToGrid($params = array())
    {
        $params = array_merge(array('type' => Mage::helper('xcom_google')->getChanneltypeCode()), $params);
        $this->_redirect('*/channel/', $params);
    }   
}