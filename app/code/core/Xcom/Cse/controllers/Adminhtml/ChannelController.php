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
 * @package     Xcom_Cse
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
require_once 'Xcom/ChannelGroup/controllers/Adminhtml/ChannelController.php';

class Xcom_Cse_Adminhtml_ChannelController extends Xcom_ChannelGroup_Adminhtml_ChannelController
{
    /**
     * Disable one or multiple channels.
     *
     * @return
     */
    public function massDisableAction()
    {
        $channelIds = $this->getRequest()->getParam('selected_channels');

        if (!is_array($channelIds)) {
             $this->_getSession()
                     ->addError($this->__('Please select channel(s)'));
            $this->_redirect('*/channel/');
            return;
        }
        try {
            $countProcessed = 0;
            foreach ($channelIds as $channelId) {
                $channel = Mage::getModel('xcom_cse/channel')->load($channelId);
                $channel->setData('is_active', 0);
                $channel->save();
                ++$countProcessed;
            }
            if ($countProcessed) {
                $this->_getSession()->addSuccess($this->__('Total of %d channel(s) were disabled', $countProcessed));
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/channel/', array(
                'type' => Mage::helper('xcom_google')->getChanneltypeCode()));
    }

    /**
     * Enable one or multiple channels.
     *
     * @return
     */
    public function massEnableAction()
    {
        $channelIds = $this->getRequest()->getParam('selected_channels');

        if (!is_array($channelIds)) {
             $this->_getSession()
                     ->addError($this->__('Please select channel(s)'));
            $this->_redirect('*/channel/');
            return;
        }
        try {
            $countProcessed = 0;
            foreach ($channelIds as $channelId) {
                $channel = Mage::getModel('xcom_cse/channel')->load($channelId);
                $channel->setData('is_active', 1);
                $channel->save();
                ++$countProcessed;
            }
            if ($countProcessed) {
                $this->_getSession()->addSuccess($this->__('Total of %d channel(s) were enabled', $countProcessed));
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/channel/', array(
                'type' => Mage::helper('xcom_google')->getChanneltypeCode()));
    }


    /**
     * @return void
     */
    public function massDisableValidationAction()
    {
        if (!$this->getRequest()->isAjax()) {
            return;
        }
        $channelIds = $this->getRequest()->getParam('selected_channels');
        if (!is_array($channelIds)) {
            $this->_setResponseMessage($this->__('Please select channel(s)'));
            return;
        }
        try {
        	// TODO:  Does this apply in Google?
            if (Mage::getResourceSingleton('xcom_cse/channel')->isProductPublishedInChannels($channelIds)) {
                $this->_setResponseMessage($this->__(
                    'Disabling the channel(s) will not remove any associated active feeds, ' .
                              'and the selected channel(s) will no longer appear on the Channel Products Page.'
                ));
            }
        } catch (Exception $e) {
            $this->_setResponseMessage($e->getMessage());
        }
    }

    /**
     * @param string $message
     * @return void
     */
    protected function _setResponseMessage($message)
    {
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
            'message' => $message
        )));
    }

}
