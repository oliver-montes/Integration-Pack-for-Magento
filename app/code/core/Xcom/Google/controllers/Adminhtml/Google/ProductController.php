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

require_once 'Xcom/ChannelGroup/controllers/Adminhtml/Channel/ProductController.php';
class Xcom_Google_Adminhtml_Google_ProductController extends Xcom_ChannelGroup_Adminhtml_Channel_ProductController
{
    /**
     * Initialize general settings for channel
     *
     * @param string $idFieldName
     * @return Xcom_Google_Model_Channel
     */
    protected function _initChannel($idFieldName = 'channel_id')
    {
        $this->_title($this->__('Channel'))
             ->_title($this->__('Google'))
             ->_title($this->__('Manage Channel'));

        $channel    = Mage::getModel('xcom_google/channel');
        $channelId  = (int)$this->getRequest()->getParam($idFieldName);
        if (!$channelId && $this->_getSession()->hasChannelId()) {
            $channelId = (int)$this->_getSession()->getChannelId();
        }
        if ($channelId) {
            $channel = Mage::getModel('xcom_google/channel')->load($channelId);
            
            // Load account associated with this channel.
            $channel->getAccount();
            
            $this->_getSession()->setChannelId($channelId);
        }
        Mage::register('current_channel', $channel);
        return $channel;
    }

    /**
     * Publish action.
     *
     * @return void
     */
    public function publishAction()
    {
        /** @var $channel Xcom_Cse_Model_Channel */
        $channel = $this->_initChannel();
        if (!$channel->getId()) {
            $this->_getSession()->addWarning($this->__('Channel must be specified'));
            $this->_redirect('*/channel_product/');
            return;
        }

        $productIds = $this->getRequest()->getParam('selected_products', array());
        if (!is_array($productIds)) {
            $productIds = explode(',', $productIds);
        }
        $previousProductIds = Mage::getModel('xcom_cseoffer/channel_product')
                ->getPreviousSubmission($channel->getId());
        $productIds = array_merge($productIds, $previousProductIds);
        
        $helper = Mage::helper('xcom_cseoffer');
		$helper->processOffers($channel, $productIds, $this->getRequest()->getPost());
        
		$this->_getSession()->addSuccess($this->__('Your request has been submitted. ' .
			'Channel Offer Status will be updated soon. Please check back.')
		);

        $this->_redirect('*/channel_product/', array('type' => $channel->getChanneltypeCode()));
    }

    /**
     * History action.
     *
     * @return void
     */
    public function historyAction()
    {
        $channel = $this->_initChannel('channel');
        if (!$channel->getId()) {
            $this->_getSession()->addWarning($this->__('Channel does not exist'));
            $this->_redirect('*/channel_product/');
            return;
        }

        if (!Mage::getSingleton('xcom_cseoffer/channel_product')->isProductsInChannel($channel->getId(),
            array($this->getRequest()->getParam('id')))) {
            $this->_getSession()->addWarning($this->__('Product not in Channel'));
            $this->_redirect('*/channel_product/');
            return;
        }

        $this->loadLayout();
        $this->_setActiveMenu('channels/products');
        $this->renderLayout();
    }

    /**
     * Action for removing products from channel
     *
     * @return void
     */
    public function massCancelAction()
    {
    	$channelId = Mage::app()->getRequest()->getParam('channel_id');
                
        /** @var $channel Xcom_Google_Model_Channel */
        $channel = Mage::getModel('xcom_google/channel')->load($channelId);

        if (!$channel) {
            Mage::throwException($this->__('Selected channel not found!'));
            return false;
        }
            	
    	try {
            $result = $this->massCancel(
                $channel,
                Mage::app()->getRequest()->getParam('selected_products')
            );

            if ($result) {
                $this->_getSession()->addSuccess(
                    $this->__('Your request to remove products from channel has been submitted. ' .
                              'Channel Offer Status will be updated soon. Please check back.')
                );
            }
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect('*/channel_product/', array('type' => $channel->getChanneltypeCode()));
    }
    
    /**
     * Remove products from a channel.
     *
     * @param $channel Xcom_Cse_Model_Channel
     * @param $productIds Xcom_CseOffer_Model_Resource_Channel_Product
     * @return bool
     */
    public function massCancel($channel, $productIds) {
    	// Re-publish the products, minus what was removed.
        $previousProductIds = Mage::getModel('xcom_cseoffer/channel_product')
        	->getPreviousSubmission($channel->getId());

        $productIdsMinusRemovedOnes = array_diff($previousProductIds, $productIds);
        
        $helper = Mage::helper('xcom_cseoffer');
        $helper->processOffers($channel, $productIdsMinusRemovedOnes, $this->getRequest()->getPost());
        
        return true;
    }
    
    /**
     * Offer Log action
     *
     * @return void
     */
    public function offererrorAction()
    {
        $this->_initChannel();

        $this->loadLayout();
        $this->_setActiveMenu('channels/products');
        $this->renderLayout();
    }

    /**
     * Check if action allowed.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('channels/products');
    }
}
