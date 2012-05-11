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

class Xcom_CseOffer_Model_Resource_Channel_Product extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('xcom_cseoffer/channel_product', 'channel_product_id');
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

            $this->_getWriteAdapter()->commit();
        } catch (Exception $e){
            $this->_getWriteAdapter()->rollBack();
            throw $e;
        }
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
     * @return Xcom_CseOffer_Model_Resource_Channel_Product
     */
    public function updateRelations($channelId, $productId, $cseItemId, $status, $link = null)
    {
        $bind = array('offer_status' => $status);

        if (null !== $cseItemId) {
            $bind['cse_item_id'] = $cseItemId;
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
     * @return Xcom_CseOffer_Model_Resource_Channel_Product
     */
    public function deleteRelations($channelId, $productId)
    {
        $where = implode(' AND ', array(
            $this->_getWriteAdapter()->quoteInto('channel_id = ?', (int)$channelId),
            $this->_getWriteAdapter()->quoteInto('product_id = ?', (int)$productId),
        ));

        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);

        return $this;
    }

    /**
     * Returns array of product ids for a given channel
     * @param int $channelId
     * @param array $productIds
     * @return array
     */
    public function getPreviousSubmission($channelId)
    {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), array('product_id'))
                ->where('channel_id = ?', (int)$channelId);
        return $this->_getReadAdapter()->fetchCol($select);
    }
    
    /**
     * Returns array of product ids.
     * Example:
     *     array(
     *         <product_id> => <cse_item_id>
     *    )
     * @param int $channelId
     * @param array $productIds
     * @return array
     */
    public function getProductOfferIds($channelId, $productIds)
    {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), array('product_id', 'cse_item_id'))
                ->where('channel_id = ?', (int)$channelId)
                ->where('cse_item_id IS NOT NULL')
                ->where('offer_status <> ?', Xcom_CseOffer_Model_Channel_Product::STATUS_INACTIVE)
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
}
