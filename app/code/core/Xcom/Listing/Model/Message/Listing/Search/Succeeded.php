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

class Xcom_Listing_Model_Message_Listing_Search_Succeeded extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Message object initialization
     */
    protected function _construct()
    {
        $this->_schemaRecordName = 'SearchListingSucceeded';
        $this->_topic = 'listing/searchSucceeded';
        parent::_construct();
    }

    /**
     * Update product statuses once message is received
     * @return array
     */
    public function process()
    {
        parent::process();
        $data = $this->getBody();
        if (empty($data['listings']) || empty($data['xProfileId'])) {
            return $this;
        }

        $channel = $this->_getChannelFromPolicy($data['xProfileId']);
        if ($channel) {
            $skus = array();
            foreach ($data['listings'] as $item) {
                if (!empty($item['product']['sku'])) {
                    $skus[$item['status']][] = $item['product']['sku'];
                }
            }
            foreach ($skus as $status => $skuArray) {
                $this->_updateProductStatus($channel, $skuArray, $this->_getStatusId($status));
            }
        }

        return $this;
    }

    /**
     * Get Magento status id by status from listing
     * @param $status
     * @return int
     */
    protected function _getStatusId($status)
    {
        switch (strtolower($status)) {
            case 'active':
                $result = Xcom_Listing_Model_Channel_Product::STATUS_ACTIVE;
                break;
            case 'inactive':
                $result = Xcom_Listing_Model_Channel_Product::STATUS_INACTIVE;
                break;
            default:
                $result = Xcom_Listing_Model_Channel_Product::STATUS_PENDING;
                break;
        }
        return $result;
    }


    /**
     * @param string $xProfileId
     * @return bool|Mage_Core_Model_Abstract
     */
    protected function _getChannelFromPolicy($xProfileId)
    {
        $policy = Mage::getModel('xcom_mmp/policy')->load($xProfileId, 'xprofile_id');
        if (!$policy->hasChannelId()) {
            return false;
        }
        $channel = Mage::getModel('xcom_mmp/channel')->load((int) $policy->getChannelId());
        if ($channel->getId()) {
            return $channel;
        }
        return false;
    }

    /**
     * @param Varien_Object $channel
     * @param array $skus
     * @param int $status
     * @return Xcom_Listing_Model_Message_Listing_Search_Response
     */
    protected function _updateProductStatus(Varien_Object $channel, array $skus, $status)
    {
        $channelProduct = Mage::getModel('xcom_listing/channel_product');
        foreach ($skus as $sku) {
            $product = Mage::helper('xcom_mmp')->getProductBySku($sku);
            if ($product->getId()) {
                $channelProduct->updateRelations(
                    $channel->getId(),
                    $product->getId(),
                    null,
                    $status
                );
            }
        }
        return $this;
    }
}
