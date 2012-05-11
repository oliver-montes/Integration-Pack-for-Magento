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
class Xcom_Listing_Model_Listing extends Mage_Core_Model_Abstract
{
    const PRICE_TYPE_MAGE = 'magentoprice';
    /**
     * Array of products which are ready for publishing.
     * Example of array:
     *  array (
     *    <product_id> => Mage_Catalog_Model_Product object
     *    <product_id> => Mage_Catalog_Model_Product object
     *  )
     *
     * @var array
     */
    protected $_products = array();

    /**
     * Example of array:
     *  array (
     *    <market_item_id> => <product_id>
     *    <market_item_id> => <product_id>
     *  )
     *
     * @var array
     */
    protected $_marketItemIds;

    protected function _construct()
    {
        $this->_init('xcom_listing/listing');
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isMagentoPriceType($type)
    {
        return self::PRICE_TYPE_MAGE == $type;
    }

    /**
     * @param array $productIds
     * @return Xcom_Listing_Model_Channel_Product
     */
    protected function _initProducts(array $productIds)
    {
        $this->_products = array();
        foreach ($productIds as $productId) {
            $product = Mage::getModel('catalog/product');
            if ($this->getChannel() instanceof Varien_Object &&
                $this->getChannel()->getStoreId()
            ) {
                $product->setStoreId($this->getChannel()->getStoreId());
            }
            $this->_products[$productId] = $product->load((int)$productId);
        }
        return $this;
    }

    /**
     * @param array $productIds
     * @return Xcom_Listing_Model_Listing
     */
    public function prepareProducts(array $productIds)
    {
        $this->_initProducts($productIds);
        foreach ($this->getProducts() as $product) {
            $product->setListingPrice($this->_calculatePrice($product));
            $product->setListingQty($this->_calculateQty($product));
            $product->setListingCategoryId($this->getCategoryId());
            $product->setListingMarketItemId($this->_getProductMarketId($product->getId()));
        }
        return $this;
    }

    /**
     * @param int $productId
     * @return int|null
     */
    protected function _getProductMarketId($productId)
    {
        $marketItemIds = $this->_getProductMarketIds();
        if (!empty($marketItemIds[$productId])) {
            return $marketItemIds[$productId];
        }
        return null;
    }

    /**
     * @return array
     */
    protected function _getProductMarketIds()
    {
        if (null === $this->_marketItemIds) {
            $this->_marketItemIds = Mage::getModel('xcom_listing/channel_product')
                ->getProductMarketIds($this->getChannelId(), array_keys($this->getProducts()));
        }
        return $this->_marketItemIds;
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->_products;
    }

    /**
     * Calculate product price for publish
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float|null
     */
    protected function _calculatePrice(Mage_Catalog_Model_Product $product)
    {
        if ($this->isMagentoPriceType($this->getPriceType())) {
            return $this->_roundPrice($product->getPrice());
        } else {
            if ($this->getPriceType() == 'markup') {
                return $this->_roundPrice($product->getPrice() + $this->_getDeltaPrice($product));
            } elseif ($this->getPriceType() == 'discount') {
                return $this->_roundPrice($product->getPrice() - $this->_getDeltaPrice($product));
            }
        }
        return null;
    }

    /**
     * @param mixed $price
     * @return float
     */
    protected function _roundPrice($price)
    {
        return round($price, 2);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    protected function _getDeltaPrice(Mage_Catalog_Model_Product $product)
    {
        if ($this->getPriceValueType() == 'percent') {
            return $this->getPriceValue() * ((float)$product->getPrice()/100);
        }
        return $this->getPriceValue();
    }

    /**
     * Calculate product quantity for publish
     *
     * @param Mage_Catalog_Model_Product $product
     * @return int|null
     */
    protected function _calculateQty(Mage_Catalog_Model_Product $product)
    {

        if ($this->getQtyValueType() == 'percent') {

            $qty = $this->getQtyValue() * ($product->getStockItem()->getQty() / 100);
            $qty = (float)(string)$qty; // EXCEPTION! Fix floating point precision!
            return (int)floor($qty);
        } elseif ($this->getQtyValueType() == 'abs') {
            return (int)floor($this->getQtyValue());
        }
        return null;
    }

    /**
     * Group products by status and presence of market_item_id.
     *
     * Products which don't have market_item_id we send as part of listing/create message.
     * In case products have market_item_id, we send them as part of listing/update message
     * If products have market_item_id but listing_status is inactive, we send them as part
     * of listing/create message
     *
     * @param array $options
     * @return Xcom_Listing_Model_Channel_Product
     */
    public function send(array $options)
    {
        $productIdsToUpdate = array_keys($this->_getProductMarketIds());
        $productIdsToCreate = array_diff(array_keys($this->getProducts()), $productIdsToUpdate);
        if (count($productIdsToCreate)) {
            $options['products'] = $this->_filterProducts($productIdsToCreate);
            Mage::helper('xcom_xfabric')->send('listing/create', $options);
        }

        if (count($productIdsToUpdate)) {
            $options['products'] = $this->_filterProducts($productIdsToUpdate);
            Mage::helper('xcom_xfabric')->send('listing/update', $options);
        }
        return $this;
    }

    /**
     * @param array $productIds
     * @return array
     */
    protected function _filterProducts(array $productIds)
    {
        $result = array();
        $products = $this->getProducts();
        foreach ($productIds as $productId) {
            if (isset($products[$productId])) {
                $result[$productId] = $products[$productId];
            }
        }
        return $result;
    }

    /**
     * @return Xcom_Listing_Model_Listing
     */
    public function saveProducts()
    {
        foreach ($this->getProducts() as $product) {
            $data = array(
                'listing_status'    => Xcom_Listing_Model_Channel_Product::STATUS_PENDING,
                'created_at'        => now(),
                'listing_id'        => (int)$this->getListingId(),
            );
            Mage::getResourceSingleton('xcom_listing/channel_product')
                ->saveRelations($this->getChannelId(), $product->getId(), $data);
        }
        return $this;
    }
}
