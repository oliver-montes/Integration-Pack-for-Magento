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

class Xcom_Listing_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    /**
     * Processing collection items after loading.
     * Modify channels, listing fields.
     *
     * @return Xcom_Listing_Model_Resource_Product_Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this as $product) {
            // Change data for channels field
            if ($product->hasChannels()) {
                $value = $product->getChannels();
                $value = explode(',', $value);
                $product->setChannels($value);
            }
            // Change data for listing field
            if ($product->hasListing()) {
                $value = $product->getListing();
                $value = explode(',', $value);
                $product->setListing($value);
            }
        }
        return $this;
    }

    /**
     * Get listings column
     *
     * @return Zend_Db_Expr
     */
    protected function _getListingStatusIdsColumn()
    {
        return new Zend_Db_Expr('GROUP_CONCAT(('
            . ' CASE'
                . ' WHEN ccp.channel_product_id IS NOT NULL THEN ccp.listing_status'
                . ' ELSE NULL'
            . ' END) ORDER BY xcc.name ASC)');
    }

    /**
     * Get listings column
     *
     * @return Zend_Db_Expr
     */
    protected function _getChannelIdsColumn()
    {
        return new Zend_Db_Expr('GROUP_CONCAT(('
            . ' CASE'
                . ' WHEN ccp.channel_product_id IS NOT NULL THEN ccp.channel_id'
                . ' ELSE NULL'
            . ' END) ORDER BY xcc.name ASC)');
    }

    /**
     * Get max_timestamp column
     *
     * @return Zend_Db_Expr
     */
    protected function _getMaxTimestampColumn()
    {
        return new Zend_Db_Expr('MAX('
            . ' CASE '
                . ' WHEN ccp.channel_product_id IS NOT NULL THEN ccp.created_at'
                . ' ELSE NULL'
            . ' END)');
    }

    /**
     * Add store qty to product collection
     *
     * @return Xcom_Listing_Model_Resource_Product_Collection
     */
    public function joinProductQty()
    {
        $this->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        return $this;
    }

    /**
     * @param string $channelTypeCode
     * @return Xcom_Listing_Model_Resource_Product_Collection
     */
    public function joinStatusColumn($channelTypeCode)
    {
        $this->getSelect()
            ->joinLeft(
                array('ccp' => $this->getTable('xcom_listing/channel_product')), 'ccp.product_id = e.entity_id', array())
            ->joinLeft(array('xcc' => $this->getTable('xcom_mmp/channel')),
                'xcc.channel_id = ccp.channel_id AND xcc.channeltype_code = :channel_type_code', array())
            ->group('e.entity_id')
            ->columns(array('ccp.channel_product_id', 'xcc.channel_id',
                'listing'       => new Zend_Db_Expr($this->_getListingStatusIdsColumn()),
                'channels'      => new Zend_Db_Expr($this->_getChannelIdsColumn()),
                'maxtimestamp'  => new Zend_Db_Expr($this->_getMaxTimestampColumn())
            ));
        $this->addBindParam('channel_type_code', $channelTypeCode);

        return $this;
    }

    /**
     * Prepare Store sensitive joins
     *
     * @param  Mage_Core_Model_Store $store
     * @return Xcom_Listing_Model_Resource_Product_Collection
     */
    public function prepareStoreSensitiveData($store)
    {
        if ($store->getId()) {
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $this->addStoreFilter($store)
                ->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner', $adminStore)
                ->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId())
                ->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId());
        } else {
            $this->addAttributeToSelect('price')
                 ->addAttributeToSelect('name')
                 ->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        }
        return $this;
    }

    /**
     * Join channel data to collection
     *
     * @param  $channelId
     * @return Xcom_Listing_Model_Resource_Product_Collection
     */
    public function joinChannelData($channelId)
    {
        $helper = Mage::helper('xcom_listing');
        $channelListingStatus = new Zend_Db_Expr('CASE'
                . ' WHEN xccp.listing_status = ' . Xcom_Listing_Model_Channel_Product::STATUS_ACTIVE
                . ' THEN \'' . $helper->__('Active') .'\''
                . ' WHEN xccp.listing_status = ' . Xcom_Listing_Model_Channel_Product::STATUS_INACTIVE
                . ' THEN \'' . $helper->__('Inactive') .'\''
                . ' WHEN xccp.listing_status = ' . Xcom_Listing_Model_Channel_Product::STATUS_FAILURE
                . ' THEN \'' . $helper->__('Failure') .'\''
                . ' WHEN xccp.listing_status = ' . Xcom_Listing_Model_Channel_Product::STATUS_PENDING
                . ' THEN \'' . $helper->__('Pending') .'\''
            . ' END'
        );
        $this->getSelect()
            ->joinLeft(array('xccp' => $this->getTable('xcom_listing/channel_product')),
                'xccp.product_id = e.entity_id AND xccp.channel_id  = :channel_id')
            ->columns(array('channel_listing_status' => $channelListingStatus));
        $this->addBindParam('channel_id', $channelId);
        return $this;
    }

    /**
     * Prepare filter for custom column max_timestamp
     *
     * @param array $condition
     * @return Xcom_Listing_Model_Resource_Product_Collection
     */
    public function prepareMaxTimestampFilter($condition)
    {
        $maxTimestamp   = $this->_getMaxTimestampColumn();
        if (isset($condition['from'])) {
            $this->getSelect()->having($maxTimestamp . ' >= :from_date');
            $this->addBindParam('from_date', $this->getConnection()->convertDateTime($condition['from']));
        }
        if (isset($condition['to'])) {
            $this->getSelect()->having($maxTimestamp . ' <= :to_date');
            $this->addBindParam('to_date', $this->getConnection()->convertDateTime($condition['to']));
        }
        return $this;
    }



    /**
     * Returns select count sql.
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $select = clone $this->getSelect();
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->columns('COUNT(DISTINCT e.entity_id) AS count_');
        $countSelect = $this->getConnection()->select()
            ->from(array('tt' => new Zend_Db_Expr(sprintf("(%s)", $select))), array('COUNT(count_)'));

        return $countSelect;
    }

    /**
     * Returns all ids.
     *
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->columns('e.'.$this->getEntity()->getIdFieldName());
        $idsSelect->limit($limit, $offset);

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * Prepare filter for custom column listings
     *
     * @param  $condition
     * @return Xcom_Listing_Model_Resource_Product_Collection
     */
    public function prepareListingsFilter($condition)
    {
        if (isset($condition['eq']) && !is_array($condition['eq'])) {
            $this->_addHavingColumn('listing_id', $condition['eq'], $this->_getListingStatusIdsColumn());
        }
        return $this;
    }

    /**
     * Prepare filter for custom column channels
     *
     * @param  $condition
     * @return Xcom_Listing_Model_Resource_Product_Collection
     */
    public function prepareChannelsFilter($condition)
    {
        if (isset($condition['eq']) && !is_array($condition['eq'])) {
            $this->_addHavingColumn('channel_id', $condition['eq'], $this->_getChannelIdsColumn());
        }
        return $this;
    }

    /**
     * @param string $columnName
     * @param string|int $bindParam
     * @param Zend_Db_Expr $findInSetExpr
     * @return Xcom_Listing_Model_Resource_Product_Collection
     */
    protected function _addHavingColumn($columnName, $bindParam, $findInSetExpr)
    {
        $findInSet = sprintf("FIND_IN_SET(:%s, %s)", $columnName, $findInSetExpr);
        $this->getSelect()->having($findInSet);
        $this->getSelect()->having($findInSetExpr . ' IS NOT NULL');
        $this->addBindParam($columnName, $bindParam);
        return $this;
    }

    /**
     * Set select order
     *
     * @param   string $field
     * @param   string $direction
     * @return  Varien_Data_Collection
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if('maxtimestamp' == $field) {
            $this->getSelect()->order($field.' '.$direction);
            return $this;
        }
        return parent::setOrder($field, $direction);
    }
}
