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

class Xcom_Listing_Model_Channel_Product extends Mage_Core_Model_Abstract
{
    const STATUS_ACTIVE    = 1;
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
        $this->_init('xcom_listing/channel_product');
    }

    /**
     * Save relation to products
     *
     * @return Xcom_Listing_Model_Channel_Product
     */
    public function saveProducts()
    {
        $date = now();
        foreach ($this->getListingProducts() as $product) {
            $data = array(
                'listing_status'    => self::STATUS_PENDING,
                'created_at'        => $date,
                'listing_id'        => (int)$this->getListingId(),
            );
            $this->_getResource()->saveRelations($this->getChannelId(), $product->getId(), $data);
        }
        return $this;
    }

    /**
     * @param int $channelId
     * @param int $productId
     * @param string $marketItemId
     * @param int $status
     * @param null|string $link
     * @return Xcom_Listing_Model_Channel_Product
     */
    public function updateRelations($channelId, $productId, $marketItemId, $status, $link = null)
    {
        $this->_getResource()->updateRelations($channelId, $productId, $marketItemId, $status, $link);
        return $this;
    }

    /**
     * @param int $channelId
     * @param int $productId
     * @return Xcom_Listing_Model_Channel_Product
     */
    public function deleteRelations($channelId, $productId)
    {
        $this->_getResource()->deleteRelations($channelId, $productId);
        return $this;
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
    public function getProductMarketIds($channelId, array $productIds)
    {
        return $this->_getResource()->getProductMarketIds($channelId, $productIds);
    }

    /**
     * @param int $channelId
     * @param array $productIds
     * @return int
     */
    public function getPublishedListingId($channelId, array $productIds)
    {
        return $this->_getResource()->getPublishedListingId($channelId, $productIds);
    }

    /**
     * Retrieve all listing ids for all products from a list from current channel
     *
     * @param array $productIds
     * @param int|null $channelId
     * @return array
     */
    public function getPublishedListingIds(array $productIds, $channelId = null)
    {
        return $this->_getResource()->getPublishedListingIds($productIds, $channelId);
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
    public function getValidListingId($channelId, array $productIds)
    {
        $listingId = $this->getPublishedListingId($channelId, $productIds);
        $publishedProductIds = $this->_getResource()->getPublishedProductIds($listingId);
        $diff1 = array_diff($publishedProductIds, $productIds);
        $diff2 = array_diff($productIds, $publishedProductIds);

        if (max(count($diff1), count($diff2))) {
            return 0;
        }
        return $listingId;
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
        return Mage::getModel('xcom_mmp/channel')->load($this->getChannelId());
    }
}
