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

require_once 'Xcom/ChannelGroup/controllers/Adminhtml/Channel/ProductController.php';
class Xcom_Ebay_Adminhtml_Ebay_ProductController extends Xcom_ChannelGroup_Adminhtml_Channel_ProductController
{
    /**
     * Initialize general settings for channel
     *
     * @param string $idFieldName
     * @return Xcom_Ebay_Model_Channel
     */
    protected function _initChannel($idFieldName = 'channel_id')
    {
        $this->_title($this->__('Channel'))
             ->_title($this->__('eBay'))
             ->_title($this->__('Manage Channel'));

        $channel    = Mage::getModel('xcom_ebay/channel');
        $channelId  = (int)$this->getRequest()->getParam($idFieldName);
        if (!$channelId && $this->_getSession()->hasChannelId()) {
            $channelId = (int)$this->_getSession()->getChannelId();
        }
        if ($channelId) {
            $channel = Mage::getModel('xcom_ebay/channel')->load($channelId);
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
        /** @var $channel Xcom_Mmp_Model_Channel */
        $channel = $this->_initChannel();
        if (!$channel->getId()) {
            $this->_getSession()->addWarning($this->__('Channel must be specified'));
            $this->_redirect('*/channel_product/');
            return;
        }

        if ($channel->getIsInactiveAccount()) {
            $this->_getSession()->addError($this->__('User ID / Merchant ID doesn\'t exist or expired.'));
        }

        if (Mage::helper('xcom_ebay')->validateIsRequiredAttributeHasMappedValue()) {
            $this->_getSession()->addError($this->__('You may have unmapped mapping for selected AttributeSet. ' .
                'Your publish may fail. Please complete mapping before hitting publish.'));
        }

        $this->loadLayout();
        $this->_setActiveMenu('channels/products');
        $this->renderLayout();
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
            $this->_getSession()->addWarning($this->__('Channel not exist'));
            $this->_redirect('*/channel_product/');
            return;
        }

        if (!Mage::getSingleton('xcom_listing/channel_product')->isProductsInChannel($channel->getId(),
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
     * Listing Log action
     *
     * @return void
     */
    public function listingerrorAction()
    {
        $this->_initChannel();

        $this->loadLayout();
        $this->_setActiveMenu('channels/products');
        $this->renderLayout();
    }

    public function saveAction()
    {
        $channel = $this->_initChannel();
        if (!$channel->getId()) {
            $this->_getSession()->addWarning($this->__('Channel must be specified'));
            $this->_redirect('*/channel_product/');
            return;
        }

        $productIds = $this->getRequest()->getParam('product_ids', array());
        if (!is_array($productIds)) {
            $productIds = explode(',', $productIds);
        }

        try {
            if (Mage::getSingleton('xcom_listing/channel_product')
                ->isProductsInChannel($channel->getId(), $productIds)) {
                $this->_processDifferentListings($channel, $productIds);
            } else {
                $this->_processNewListing($channel, $productIds);
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/publish', array('_current'  => true));
            return;
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/publish', array('_current'  => true));
            return;
        }

        $this->_getSession()->addSuccess($this->__('Your request has been submitted. ' .
            'Channel Listing Status will be updated soon. Please check back.')
        );

        $this->_redirect('*/channel_product/', array(
            'type'   => $channel->getChanneltypeCode(),
            'store'  => $this->getRequest()->getParam('store')
        ));
    }


    /**
     * Categories JSON action for tree
     */
    public function categoriesJsonAction()
    {
        $this->_initChannel();
        /** @var $block Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_CategoriesTree */
        $block = $this->getLayout()->createBlock('xcom_ebay/adminhtml_product_edit_tab_settings_categoriesTree');
        $block->setCategoriesParentId($this->getRequest()->getParam('node'));
        $this->getResponse()->appendBody($block->getTreeJson());
    }

    /**
     * @param Xcom_Ebay_Model_Channel $channel
     * @param array $productIds
     * @return Xcom_Ebay_Adminhtml_Ebay_ProductController
     */
    protected function _processNewListing($channel, array $productIds)
    {
        /** @var $listing Xcom_Mmp_Model_Listing */
        $listing = Mage::getModel('xcom_listing/listing');
        $listing->addData($this->getRequest()->getPost());
        $listing->setChannel($channel);
        $listing->prepareProducts($productIds);
        /** @var $validator Xcom_Mmp_Helper_Listing_Validator */
        $validator = Mage::helper('xcom_listing/validator');
        $validator->setListing($listing);
        $validator->validateFields();
        $validator->validateProducts();
        $options = array(
            'policy'  => Mage::getModel('xcom_ebay/policy')->load((int)$this->getRequest()->getPost('policy_id')),
            'channel' => $channel
        );
        $listing->send($options);
        $listing->save();
        $listing->saveProducts();
        return $this;
    }

    /**
     * @param Xcom_Ebay_Model_Channel $channel
     * @param array $productIds
     * @throws Mage_Core_Exception
     */
    protected function _processDifferentListings($channel, array $productIds)
    {
        $listingIds = Mage::getSingleton('xcom_listing/channel_product')
            ->getPublishedListingIds($productIds, $channel->getId());
        /** @var $validator Xcom_Listing_Helper_Validator */
        $validator = Mage::helper('xcom_listing/validator');
        foreach ($listingIds as $listingId => $listingProductData) {
            /** @var $listing Xcom_Mmp_Model_Listing */
            $listing = Mage::getModel('xcom_listing/listing');
            $listing->load($listingId);
            $previousData = $listing->getData();

            /** @var $newListing Xcom_Mmp_Model_Listing */
            $newListing = Mage::getModel('xcom_listing/listing');
            $newListing->addData($this->getRequest()->getPost());
            $newListing->setChannel($channel);
            $validator->setListing($newListing);
            $validator->validateOptionalFields();

            //prepareData
            unset($previousData['listing_id']);
            $listingData = array_merge($previousData, $this->getRequest()->getPost());
            if ($newListing->isMagentoPriceType($listingData['price_type'])) {
                unset($listingData['price_value']);
                unset($listingData['price_value_type']);
            }

            $newListing->addData($listingData);
            $newListing->prepareProducts($listingProductData['product_ids']);
            $validator->setListing($newListing);
            $validator->validateProducts($validator->isPriceChanged(), $validator->isQtyChanged());

            $options = array(
                'policy'  => Mage::getModel('xcom_ebay/policy')->load((int)$listingData['policy_id']),
                'channel' => $channel
            );
            $newListing->send($options);
            $newListing->save();
            $newListing->saveProducts();
        }
    }

    /**
     * Action for cancel/remove products from channel
     *
     * @return void
     */
    public function massCancelAction()
    {
        try {
            $result = $this->massCancel(
                Mage::app()->getRequest()->getParam('channel_id'),
                Mage::app()->getRequest()->getParam('selected_products')
            );

            if ($result) {
                $this->_getSession()->addSuccess(
                    $this->__('Your request to remove products from channel has been submitted. ' .
                              'Channel Listing Status will be updated soon. Please check back.')
                );
            }
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect('*/channel_product/index',
            array('store' => $this->getRequest()->getParam('store')));
    }

    /**
     * Cancel products from a channel.
     *
     * @param $channelId Xcom_Mmp_Model_Channel
     * @param $productIds Xcom_Listing_Model_Resource_Channel_Product
     * @return bool
     */
    public function massCancel($channelId, $productIds) {
        /** @var $channel Xcom_Ebay_Model_Channel */
        $channel = Mage::getModel('xcom_ebay/channel')->load($channelId);

        if (!$channel) {
            Mage::throwException($this->__('Selected channel not found!'));
            return false;
        }

        /** @var $channelProducts Xcom_Listing_Model_Resource_Channel_Product_Collection */
        $channelProducts = Mage::getModel('xcom_listing/channel_product')
            ->getCollection()
            ->addFieldToFilter('product_id', array('in' => $productIds))
            ->addFieldToFilter('channel_id', $channelId)
            ->load();

        if (count($channelProducts) == 0) {
            Mage::throwException($this->__('No channel products found!'));
            return false;
        }

        $productsByListing = array();
        $rejectedChannelProductIds = array();
        $skusByListing = array();
        /** @var $channelProduct Xcom_Listing_Model_Channel_Product */
        foreach ($channelProducts as $channelProduct) {
            switch ($channelProduct->getListingStatus()) {
                case Xcom_Listing_Model_Channel_Product::STATUS_PENDING:
                case Xcom_Listing_Model_Channel_Product::STATUS_INACTIVE:
                    $rejectedChannelProductIds[] = $channelProduct->getProductId();
                    break;
                default:
                    $listingId = $channelProduct->getListingId();
                    if (!array_key_exists($listingId, $skusByListing)) {
                        $skusByListing[$listingId] = array();
                        $productsByListing[$listingId] = array();
                    }
                    $product = $channelProduct->getCatalogProduct();
                    $skusByListing[$listingId][] = $product->getSku();
                    $productsByListing[$listingId][] = $product;
            }
        }

        if (count($rejectedChannelProductIds) > 0) {
            Mage::throwException(
                $this->__('Rejected Product IDs: %s. The listing is not active', join($rejectedChannelProductIds, ','))
            );

            if (count($rejectedChannelProductIds) == count($productIds)) {
                return false;
            }
        }

        foreach ($skusByListing as $listingId => $skus) {
            $xProfileId = Mage::getResourceModel('xcom_listing/listing')
                ->getXprofileIdByListingId($listingId);
            Mage::helper('xcom_xfabric')->send('listing/cancel', array(
                    'skus'       => (array) $skus,
                    'policy_id'  => $xProfileId,
                    'products' => $productsByListing[$listingId],
                    'channel' => $channel
                )
            );
        }

        foreach ($channelProducts as $channelProduct) {
            $channelProduct
                ->setLink(NULL)
                ->setListingStatus(Xcom_Listing_Model_Channel_Product::STATUS_PENDING)
                ->save();
        }

        return true;
    }

    /**
     * Check if action allowed.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('channels/products')
            && Mage::helper('xcom_ebay')->isExtensionEnabled();
    }
}
