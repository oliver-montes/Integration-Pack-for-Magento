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

class Xcom_Listing_Model_Resource_Channel_Product_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initialize resource model for collection.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('xcom_listing/channel_product');
    }

    public function addChannelFilter($channelId)
    {
        $this->getSelect()->where('channel_id=?', $channelId);
        return $this;
    }

    public function addProductFilter($productIds = array())
    {
        $this->getSelect()->where('product_id IN (?)', $productIds);
        return $this;
    }

    /**
     * Add columns from catalog products to each channel product.
     *
     * @return Xcom_Listing_Model_Resource_Channel_Product_Collection
     */
    public function addCatalogProducts()
    {
        $this->getSelect()->joinLeft(
            array('cp' => $this->getTable('catalog/product')),
            'main_table.product_id = cp.entity_id'
        );
        return $this;
    }

    public function addSkusFilter($skus)
    {
        return $this->addCatalogProducts()->addFieldToFilter('cp.sku', array('IN' => $skus));
    }

    /**
     * Add table xcom_listing_channel_product to join and filter by product_id
     *
     * @param $productId
     * @return Xcom_Listing_Model_Resource_Channel_Product_Collection
     */
    public function addChannelProduct($productId)
    {
        $select = $this->getSelect();
        $select->join(array('ch' => $this->getTable('xcom_mmp/channel')),
                         'main_table.channel_id = ch.channel_id');
        $select->where('main_table.product_id = ?', (int)$productId);
        return $this;
    }

    /**
     * Return options
     *
     * @return array
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash('channel_id', 'name');
    }
}
