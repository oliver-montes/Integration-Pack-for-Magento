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
 * @package     Xcom_Mmp
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Xcom_Mmp_Model_Resource_Channel extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('xcom_mmp/channel', 'channel_id');
    }

    /**
     * Rewrite load select method with custom join
     *
     * @param string $field
     * @param string|int|array $value
     * @param object $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select =  parent::_getLoadSelect($field, $value, $object);
        $select->joinLeft(
                array('ca' => Mage::getModel($object->getAccountModelClass())->getResource()->getMainTable()),
                $this->getMainTable() . '.account_id = ca.account_id',
                array('is_inactive_account'  =>
                    new Zend_Db_Expr('CASE WHEN (DATEDIFF(ca.validated_at, NOW()) < 0 OR ca.validated_at=0'
                        . ' OR ca.status < 1) THEN 1 ELSE 0 END')
                ));
        return $select;
    }

    /**
     * Check if channel with the same "Name" already exist.
     *
     * @param Xcom_Mmp_Model_Channel $channel
     * @return bool
     */
    public function isChannelNameUnique(Xcom_Mmp_Model_Channel $channel)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('count' => new Zend_Db_Expr('COUNT(channel_id)')))
            ->where($this->_getReadAdapter()->quoteIdentifier('name') . ' = ?', $channel->getName());
        if ($channel->getId()) {
            $select->where($this->_getReadAdapter()->quoteIdentifier('channel_id') . ' != ?', $channel->getId());
        }
        return $this->_getReadAdapter()->fetchOne($select) ? false : true;
    }

    /**
     * Check if channel with the same combination of "Store View", "Site" parameters already exist.
     *
     * @param Xcom_Mmp_Model_Channel $channel
     * @return bool
     */
    public function isChannelStoreSiteUnique(Xcom_Mmp_Model_Channel $channel)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('count' => new Zend_Db_Expr('COUNT(channel_id)')))
            ->where('store_id = ?', $channel->getStoreId())
            ->where('site_code = ?', $channel->getSiteCode())
            ->where('account_id = ?', $channel->getAccountId());
        return $this->_getReadAdapter()->fetchOne($select) ? false : true;
    }

    /**
     * 1. Check if channels with desired account ID are exists.
     * 2. Check if channel is Active|Inactive.
     *
     * @param $accountId
     * @param bool|null $isActive
     * @return bool
     */
    public function validateChannelsByAccountId($accountId, $isActive = null)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('account_id=?', $accountId);
        if ($isActive) {
            $select->where('is_active=?', ($isActive ? 1 : 0));
        }
        if (false != ($data = $this->_getReadAdapter()->fetchAll($select))) {
            return true;
        }
        return false;
    }

     /**
     * Check whether channel has at least one published product.
     * Returns false if no products found
     *
     * @param array $channelIds
     * @return bool
     */
    public function isProductPublishedInChannels(array $channelIds)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('xcom_listing/channel_product'), new Zend_Db_Expr('COUNT(product_id)'))
            ->where($this->_getReadAdapter()->quoteIdentifier('channel_id') . ' IN (?)', $channelIds);
        return $this->_getReadAdapter()->fetchOne($select) ? true : false;
    }

    /**
     * @param $xProfileId string
     * @return int|bool
     */
    public function getIdByXProfileId($xProfileId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table' => $this->getMainTable()), 'channel_id')
            ->joinLeft(array('pol' => $this->getTable('xcom_mmp/policy')),
                'pol.channel_id=main_table.channel_id')
            ->where('pol.xprofile_id=?', $xProfileId);

        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Check if channel with combination of eBay Site + eBay Account already exist and
     * if this (existed) channel has the same Store View value as channel in argument.
     *
     * @param Xcom_Mmp_Model_Channel $channel
     * @return bool
     */
    public function isChannelStoreViewDiffers(Xcom_Mmp_Model_Channel $channel)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('store_id'))
            ->where('site_code = ?', $channel->getSiteCode())
            ->where('account_id = ?', $channel->getAccountId());
        $storeId = $this->_getReadAdapter()->fetchOne($select);
        if (empty($storeId) || ($storeId == $channel->getStoreId())) {
            return false;
        }
        return true;
    }

    /**
     * Identify channel by xAccount id and site code.
     * And retrieve store view id then.
     *
     * @param int $xaccountId
     * @param string $siteCode
     * @return null|int
     */
    public function getStoreId($xaccountId, $siteCode)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table' => $this->getMainTable()), array('store_id'))
            ->joinLeft(array('xma' => $this->getTable('xcom_mmp/account')),
                'xma.account_id = main_table.account_id' .
                    $this->_getReadAdapter()->quoteInto(' AND xma.xaccount_id = ?', $xaccountId),
                array())
            ->where('main_table.site_code = ?', $siteCode);
        return $this->_getReadAdapter()->fetchOne($select);
    }
}
