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
 * @package     Xcom_CseOffer
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_CseOffer_Model_Message_Cse_Offer_Response extends Xcom_Xfabric_Model_Message_Response
{
    /** @var status in product grid */
    protected $_newChannelProductStatus;

    /** @var  status in history table*/
    protected $_newChannelHistoryStatus;

    /** @var $_channel Xcom_Cse_Model_Resource_Channel */
    protected $_channel;

    /** @var $_channelProducts Xcom_CseOffer_Model_Resource_Channel_Product_Collection */
    protected $_channelProducts = array();


    /**
     * Process incoming message
     * @return bool
     */
    public function process()
    {
        if (!$this->_checkResponse()) {
            return false;
        }

        $productIds = Mage::getModel('xcom_cseoffer/channel_product')
                ->getPreviousSubmission($this->_channel->getId());
        $this->_initChannelProducts($productIds);
        $this->_updateChannelProducts();
        $logResponse = $this->_saveLogResponse();
        $this->_updateHistory($logResponse->getId());

        parent::process();
        return true;
    }

    /**
     * Init channel products
     * @param $skuArray array
     */
    protected function _initChannelProducts(array $productIds)
    {
        foreach ($productIds as $productId) {
            $this->_channelProducts[] = $this->_loadProduct($productId);
        }
    }

    /**
     * save response to log table
     * @return Xcom_CseOffer_Model_Message_Cse_Offer_Log_Response
     */
    protected function _saveLogResponse()
    {
        return Mage::getModel('xcom_cseoffer/message_cse_offer_log_response')
            ->setResponseBody(json_encode($this->getBody()))
            ->setCorrelationId($this->getCorrelationId())
            ->save();
    }

    /**
     * load product by sku
     * @param string $sku
     * @return Mage_Core_Model_Abstract
     */
    protected function _loadProduct($productId)
    {
        $product = Mage::getModel('catalog/product');
        if ($productId) {
            $product->load((int)$productId);
        }
        return $product;
    }

    /**
     * Check whether message have all needed data
     * @return bool
     */
    protected function _checkResponse()
    {
        $data = $this->getBody();
        if ($this->getCorrelationId() == null) {
            Mage::throwException('No Correlation ID in Response' . print_r($this->getHeaders(), true));
        }

        if (isset($data['xAccountId']) && strlen($data['xAccountId']) > 0) {
            $xAccountId = $data['xAccountId'];
        } else {
            Mage::throwException('No xAccountId in Response!');
        }
        if (isset($data['siteId']) && strlen($data['siteId']) > 0) {
            $siteId = $data['siteId'];
        } else {
            Mage::throwException('No siteId in Response!');
        }
        if (isset($data['offerName']) && strlen($data['offerName']) > 0) {
            $offerName = $data['offerName'];
        } else {
            Mage::throwException('No offerName in Response!');
        }
        
        /** @var $channel Xcom_Cse_Model_Channel */
        $channel = Mage::getModel('xcom_cse/channel');
        $channelId = $channel->getIdByKey($xAccountId, $siteId, $offerName);
        if (!$channelId) {
            Mage::throwException('Channel with xAccountId, siteId, and offerName doesn\'t exists: ' . $xAccountId);
        }
        $this->_channel = $channel->load($channelId);

        return true;
    }

    /**
     * Get request log by correlation id
     * @return Mage_Core_Model_Abstract
     */
    protected function _getRelatedLogRequest()
    {
        return Mage::getModel('xcom_cseoffer/message_cse_offer_log_request')
            ->load($this->getCorrelationId(), 'correlation_id');
    }

    /**
     * Update channel history
     *
     * @param $logResponseId
     * @return Xcom_CseOffer_Model_Message_Cse_Offer_Response
     */
    protected function _updateHistory($logResponseId)
    {
        foreach ($this->_getHistoryCollection() as $historyItem) {
            $historyItem
                ->setLogResponseId($logResponseId)
                ->setResponseResult($this->_newChannelHistoryStatus)
                ->save();
        }
        return $this;
    }

    /**
     * Get collection of history items
     * @return Xcom_CseOffer_Model_Resource_Channel_History_Collection
     */
    protected function _getHistoryCollection()
    {
        $result = array();
        $productIds = $this->_getProductIds();
        if ($this->_getRelatedLogRequest()->getId() && !empty($productIds)) {
            $collection = Mage::getResourceModel('xcom_cseoffer/channel_history_collection');
            $collection->addFieldToFilter('log_request_id', $this->_getRelatedLogRequest()->getId());
            $result = $collection->addFieldToFilter('product_id', array('IN' => $productIds));
        }
        return $result;
    }


    /**
     * Update product status
     *
     * @return Xcom_CseOffer_Model_Message_Cse_Offer_Response
     */
    protected function _updateChannelProducts()
    {
        foreach ($this->_channelProducts as $product) {
            Mage::getSingleton('xcom_cseoffer/channel_product')->updateRelations(
                $this->_channel->getId(),
                $product->getId(),
                $product->getCseItemId(),
                $this->_newChannelProductStatus,
                $product->getLink()
            );
        }
        return $this;
    }

    /**
     * Collect product ids from channel products
     * @return array
     */
    protected function _getProductIds()
    {
        $productIds = array();
        foreach ($this->_channelProducts as $channelProduct) {
            $productIds[] = $channelProduct->getId();
        }
        return $productIds;
    }
}
