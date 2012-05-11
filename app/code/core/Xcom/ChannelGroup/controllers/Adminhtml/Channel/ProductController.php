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
 * @package    Xcom_ChannelGroup
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'Xcom/ChannelGroup/controllers/Adminhtml/HandleAction.php';

/**
 * Controller for publishing products to a channel
 */
class Xcom_ChannelGroup_Adminhtml_Channel_ProductController extends Xcom_ChannelGroup_Adminhtml_HandleAction
{
   /**
    * Channel Products page action.
    *
    * @return void
    */
    public function indexAction()
    {
        //set default store ID via getting the first one store
        if (null === $this->getRequest()->getParam('store')) {
            /** @var $helper Xcom_ChannelGroup_Helper_Data */
            $helper = Mage::helper('xcom_channelgroup');
            $this->getRequest()->setParam('store', $helper->getFirstStoreId());
        }

        $this->_handleName = 'ADMINHTML_CHANNEL_PRODUCT';
        $this->_title($this->__('Channels'))
             ->_title($this->__('Channel Products'));

        // Clear session values.
        // We are using this values for "Publish Settings" page.
        $this->_getSession()->unsChannelId();
        $this->_getSession()->unsChannelProducts();

        $this->_initLayout();

        $this->_setActiveMenu('channels/products');
        $this->renderLayout();
    }

    /**
     * Channel products grid for AJAX request.
     *
     * @return void
     */
    public function gridAction()
    {
        $this->_initLayout();

        $blockName = "xcom_{$this->getChannelType()->getCode()}/adminhtml_product_grid";
        $block = $this->getLayout()->createBlock($blockName);
        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }
}
