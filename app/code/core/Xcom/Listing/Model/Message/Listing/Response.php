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
 * @package     Xcom_Listing
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Listing_Model_Message_Listing_Response extends Xcom_Xfabric_Model_Message_Response
{
    /** @var status in product grid */
    protected $_newChannelProductStatus;

    /** @var  status in history table*/
    protected $_newChannelHistoryStatus;

    /** @var $_channel Xcom_Mmp_Model_Resource_Channel */
    protected $_channel;

    /** @var $_channelProducts Xcom_Listing_Model_Resource_Channel_Product_Collection */
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

        $skuArray = $this->_collectProductSkus();
        $this->_initChannelProducts($skuArray);
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
    protected function _initChannelProducts(array $skuArray)
    {
        foreach ($skuArray as $sku) {
            $this->_channelProducts[] = Mage::helper('xcom_mmp')->getProductBySku($sku);
        }
    }

    /**
     * save response to log table
     * @return Xcom_Listing_Model_Message_Listing_Log_Response
     */
    protected function _saveLogResponse()
    {
        return Mage::getModel('xcom_listing/message_listing_log_response')
            ->setResponseBody(json_encode($this->getBody()))
            ->setCorrelationId($this->getCorrelationId())
            ->save();
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

        if (isset($data['xProfileId']) && strlen($data['xProfileId']) > 0) {
            $xProfileId = $data['xProfileId'];
        } else {
            Mage::throwException('No xProfileId in Response!');
        }

        /** @var $channel Xcom_Mmp_Model_Channel */
        $channel = Mage::getModel('xcom_mmp/channel');
        $channelId = $channel->getIdByXProfileId($xProfileId);
        if (!$channelId) {
            Mage::throwException('Channel with xProfileId doesn\'t exists: ' . $xProfileId);
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
        return Mage::getModel('xcom_listing/message_listing_log_request')
            ->load($this->getCorrelationId(), 'correlation_id');
    }

    /**
     * Update channel history
     *
     * @param $logResponseId
     * @return Xcom_Listing_Model_Message_Listing_Response
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
     * @return Xcom_Listing_Model_Resource_Channel_History_Collection
     */
    protected function _getHistoryCollection()
    {
        $result = array();
        $productIds = $this->_getProductIds();
        if ($this->_getRelatedLogRequest()->getId() && !empty($productIds)) {
            $collection = Mage::getResourceModel('xcom_listing/channel_history_collection');
            $collection->addFieldToFilter('log_request_id', $this->_getRelatedLogRequest()->getId());
            $result = $collection->addFieldToFilter('product_id', array('IN' => $productIds));
        }

        return $result;
    }


    /**
     * Update product status
     *
     * @return Xcom_Listing_Model_Message_Listing_Response
     */
    protected function _updateChannelProducts()
    {
        foreach ($this->_channelProducts as $product) {
            Mage::getSingleton('xcom_listing/channel_product')->updateRelations(
                $this->_channel->getId(),
                $product->getId(),
                $product->getMarketItemId(),
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

    protected function _collectProductSkus()
    {
        return $this;
    }
}
