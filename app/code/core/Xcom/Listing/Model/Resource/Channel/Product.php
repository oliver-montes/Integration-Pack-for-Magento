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

class Xcom_Listing_Model_Resource_Channel_Product extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('xcom_listing/channel_product', 'channel_product_id');
    }

    /**
     * Save channel products.
     *
     * @throws Exception|Mage_Core_Exception
     * @param $channelId
     * @param $productId
     * @param array $channelData
     * @return void
     */
    public function saveRelations($channelId, $productId, $channelData = array())
    {
        $this->_getWriteAdapter()->beginTransaction();
        try {
            $channelProductId = $this->_getChannelProductId($channelId, $productId);

            $dataForSave = new Varien_Object();
            $dataForSave->addData($channelData);

            if ($channelProductId) {
                // Update
                $this->_getWriteAdapter()->update($this->getMainTable(),
                    $this->_prepareDataForTable($dataForSave, $this->getMainTable()),
                    array(
                        'channel_id = ' . (int) $channelId,
                        'product_id = ' . (int) $productId
                    ));
            } else {
                $dataForSave->setChannelId((int)$channelId);
                $dataForSave->setProductId((int)$productId);
                //Insert
                $this->_getWriteAdapter()->insert($this->getMainTable(),
                    $this->_prepareDataForTable($dataForSave, $this->getMainTable()));
            }
            $this->_cleanListingTable();

            $this->_getWriteAdapter()->commit();
        } catch (Exception $e){
            $this->_getWriteAdapter()->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * @return Xcom_Listing_Model_Resource_Channel_Product
     */
    protected function _cleanListingTable()
    {
        $adapter = $this->_getReadAdapter();
        /** @var $select Varien_Db_Select */
        $select = $this->_getReadAdapter()->select()
            ->from(array('listing' => $this->getTable('xcom_listing/listing')), array('cp.listing_id'))
            ->joinLeft(array('cp' => $this->getMainTable()),
                $adapter->quoteIdentifier('cp.listing_id') . ' = ' . $adapter->quoteIdentifier('listing.listing_id'),
                array())
            ->where($adapter->quoteIdentifier('cp.listing_id') . ' IS NULL');

        $adapter->query($select->deleteFromSelect('listing'));

        return $this;
    }

    protected function _getChannelProductId($channelId, $productId)
    {
        $select =  $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('channel_product_id'))
            ->where('channel_id = ?', (int)$channelId)
            ->where('product_id = ?', (int)$productId);

        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Update product relations.
     *
     * @param int $channelId
     * @param int $productId
     * @param int $marketItemId
     * @param int $status
     * @param string $link
     * @return Xcom_Listing_Model_Resource_Channel_Product
     */
    public function updateRelations($channelId, $productId, $marketItemId, $status, $link = null)
    {
        $bind = array('listing_status' => $status);

        if (null !== $marketItemId) {
            $bind['market_item_id'] = $marketItemId;
        }
        if (null !== $link) {
            $bind['link'] = $link;
        }

        $where = array(
            $this->_getWriteAdapter()->quoteInto('channel_id = ?', (int)$channelId),
            $this->_getWriteAdapter()->quoteInto('product_id = ?', (int)$productId),
        );

        $this->_getWriteAdapter()->update($this->getMainTable(), $bind, $where);
        return $this;
    }

    /**
     * Delete product relations.
     *
     * @param int $channelId
     * @param int $productId
     * @return Xcom_Listing_Model_Resource_Channel_Product
     */
    public function deleteRelations($channelId, $productId)
    {
        $where = implode(' AND ', array(
            $this->_getWriteAdapter()->quoteInto('channel_id = ?', (int)$channelId),
            $this->_getWriteAdapter()->quoteInto('product_id = ?', (int)$productId),
        ));

        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
        $this->_cleanListingTable();

        return $this;
    }


    /**
     * Returns array of product ids.
     * Example:
     *     array(
     *         <product_id> => <market_item_id>
     *         <product_id> => <market_item_id>
     *    )
     * @param int $channelId
     * @param array $productIds
     * @return array
     */
    public function getProductMarketIds($channelId, $productIds)
    {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), array('product_id', 'market_item_id'))
                ->where('channel_id = ?', (int)$channelId)
                ->where('market_item_id IS NOT NULL')
                ->where('listing_status <> ?', Xcom_Listing_Model_Channel_Product::STATUS_INACTIVE)
                ->where('product_id IN (?)', $productIds);
        return $this->_getReadAdapter()->fetchPairs($select);
    }

    /**
     * Retrieve attribute sets for products
     *
     * @param array $productIds
     * @return array
     */
    public function getProductAttributeSets(array $productIds)
    {
        $select     = $this->_getReadAdapter()->select()
            ->from(array('cpe' => $this->getTable('catalog/product')), array('attribute_set_id'))
            ->where('cpe.entity_id IN (?)', $productIds)
            ->distinct(true);

        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * @param int $channelId
     * @param array $productIds
     * @return int
     */
    public function getPublishedListingId($channelId, array $productIds)
    {
        sort($productIds);

        $sumExpr = new Zend_Db_Expr(
            sprintf('SUM(CASE WHEN FIND_IN_SET(%s, %s) > 0 THEN 1 ELSE 0 END)',
                $this->_getReadAdapter()->quoteIdentifier('product_id'),
                $this->_getReadAdapter()->quote(implode(',', $productIds)))
        );

        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('listing_id'))
            ->where($this->_getReadAdapter()->quoteIdentifier('channel_id') . ' = ?', (int)$channelId)
            ->group('listing_id')
            ->having($sumExpr . ' = ' . count($productIds));

        return (int)$this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Return array with listing's ids and products published to each one
     * Example:
     *      array(
     *          <listing_id> => array(
     *              <product_id>, ...
     *          ), ...
     *      )
     *
     * @param array|null $productIds
     * @param int $channelId
     * @return int
     */
    public function getPublishedListingIds(array $productIds, $channelId = null)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array(
                'listing_id', 'product_id', 'channel_id'
            ))
            ->where($this->_getReadAdapter()->quoteIdentifier('product_id') . ' IN (?)', $productIds);
        if ($channelId) {
            $select->where($this->_getReadAdapter()->quoteIdentifier('channel_id') . ' = ?', (int)$channelId);
        }

        $listings = array();
        foreach ($this->_getReadAdapter()->fetchAll($select) as $pair) {
            if (!isset($listings[$pair['listing_id']])) {
                $listings[$pair['listing_id']] = array();
            }
            $listings[$pair['listing_id']]['product_ids'][] = $pair['product_id'];
            $listings[$pair['listing_id']]['channel_id']    = $pair['channel_id'];
        }

        return $listings;
    }

    /**
     * Check whether all passed products are published to the given channel
     *
     * @param int $channelId
     * @param array $productIds
     * @param int status
     * @return boolean
     */
    public function isProductsInChannel($channelId, array $productIds)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array(
                'cnt' => new Zend_Db_Expr('COUNT(*)')
            ))
            ->where($this->_getReadAdapter()->quoteIdentifier('channel_id') . ' = ?', (int)$channelId)
            ->where($this->_getReadAdapter()->quoteIdentifier('product_id') . ' IN (?)', $productIds)
            ->having('cnt = ' . count($productIds));

        return $this->_getReadAdapter()->fetchOne($select) === false ? false : true;
    }

    /**
     * @param int $listingId
     * @return array
     */
    public function getPublishedProductIds($listingId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('product_id'))
            ->where($this->_getReadAdapter()->quoteIdentifier('listing_id') . ' = ?', (int)$listingId);
        return $this->_getReadAdapter()->fetchCol($select);
    }
}
