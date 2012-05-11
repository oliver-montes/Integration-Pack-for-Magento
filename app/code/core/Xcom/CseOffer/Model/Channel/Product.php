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

class Xcom_CseOffer_Model_Channel_Product extends Mage_Core_Model_Abstract
{
    const STATUS_ACTIVE		= 1;
    const STATUS_INACTIVE   = 2;
    const STATUS_FAILURE    = 3;
    const STATUS_PENDING    = 4;
    const STATUS_UNLISTED   = '';

    /**
     * Initialize class.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('xcom_cseoffer/channel_product');
    }

    /**
     * @return Xcom_CseOffer_Model_Channel_Product
     */
    public function saveProducts()
    {
        foreach ($this->getOfferProducts() as $product) {
            $data = array(
                'offer_status'    => self::STATUS_PENDING,
                'created_at'        => now(),
                'offer_id'        => (int)$this->getOfferId(),
            );
            $this->_getResource()->saveRelations($this->getChannelId(), $product->getId(), $data);
        }
        return $this;
    }

    /**
     * @param int $channelId
     * @param int $productId
     * @param string $offerId
     * @param int $status
     * @param null|string $link
     * @return Xcom_CseOffer_Model_Channel_Product
     */
    public function updateRelations($channelId, $productId, $offerId, $status, $link = null)
    {
        $this->_getResource()->updateRelations($channelId, $productId, $offerId, $status, $link);
        return $this;
    }

    /**
     * @param int $channelId
     * @param int $productId
     * @return Xcom_CseOffer_Model_Channel_Product
     */
    public function deleteRelations($channelId, $productId)
    {
        $this->_getResource()->deleteRelations($channelId, $productId);
        return $this;
    }

    /**
     * @param int $channelId
     * @param array $productIds
     * @return array
     */
    public function getPreviousSubmission($channelId)
    {
        return $this->_getResource()->getPreviousSubmission($channelId);
    }
    
    /**
     * Retrieve attribute sets for products.
     *
     * @param array $productIds
     * @return array
     */
    public function getProductAttributeSets(array $productIds)
    {
        return $this->_getResource()->getProductAttributeSets($productIds);
    }

    /**
     * @param int $channelId
     * @param array $productIds
     * @return array
     */
    public function getProductOfferIds($channelId, array $productIds)
    {
        return $this->_getResource()->getProductOfferIds($channelId, $productIds);
    }

    /**
     * @param int $channelId
     * @param array $productIds
     * @return int
     */
    public function getPublishedOfferId($channelId, array $productIds)
    {
        return $this->_getResource()->getPublishedOfferId($channelId, $productIds);
    }

    /**
     * Retrieve all offer ids for all products from a list from current channel
     *
     * @param array $productIds
     * @param int|null $channelId
     * @return array
     */
    public function getPublishedOfferIds(array $productIds, $channelId = null)
    {
        return $this->_getResource()->getPublishedOfferIds($productIds, $channelId);
    }

    /**
     *  Check whether all passed products are published to the given channel
     *
     * @param int $channelId
     * @param array $productIds
     * @param int $status
     * @return boolean
     */
    public function isProductsInChannel($channelId, array $productIds, $status = null)
    {
        return $this->_getResource()->isProductsInChannel($channelId, $productIds, $status);
    }

    /**
     * @param int $channelId
     * @param array $productIds
     * @return int
     */
    public function getValidOfferId($channelId, array $productIds)
    {
        $offerId = $this->getPublishedOfferId($channelId, $productIds);
        $publishedProductIds = $this->_getResource()->getPublishedProductIds($offerId);
        $diff1 = array_diff($publishedProductIds, $productIds);
        $diff2 = array_diff($productIds, $publishedProductIds);

        if (max(count($diff1), count($diff2))) {
            return 0;
        }
        return $offerId;
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function getCatalogProduct()
    {
        return Mage::getModel('catalog/product')->load($this->getProductId());
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function getChannel()
    {
        return Mage::getModel('xcom_cse/channel')->load($this->getChannelId());
    }
}
