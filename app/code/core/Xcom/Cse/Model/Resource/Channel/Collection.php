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
 * @package     Xcom_Cse
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Cse_Model_Resource_Channel_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initialize resource model for collection
     * @return void
     */
    public function _construct()
    {
        $this->_init('xcom_cse/channel');
    }

    /**
     * Add filter to select by code.
     *
     * @param string $channelTypeCode
     * @return Xcom_Cse_Model_Resource_Channel_Collection
     */
    public function addChanneltypeCodeFilter($channelTypeCode)
    {
        return $this->addFieldToFilter('main_table.channeltype_code', $channelTypeCode);
    }

    /**
     * Join core_store table with main_table.
     * Add website_id and group_id fields.
     *
     * @return Xcom_Cse_Model_Resource_Channel_Collection
     */
    public function addWebsiteStoreInfo()
    {
        $this->getSelect()->join(
            array('s' => $this->getTable('core/store')),
            'main_table.store_id = `s`.store_id',
            array('website_id', 'group_id'));
        return $this;
    }

    /**
     * Retrieve hash of channels.
     *
     * @param string $value
     * @param string $label
     * @return array
     */
    public function toOptionHash($value = 'channel_id', $label = 'name')
    {
        return $this->_toOptionHash($value, $label);
    }

    /**
     * Retrieve array of channels.
     *
     * @param string $value
     * @param string $label
     * @return array
     */
    public function toOptionArray($value = 'channel_id', $label = 'name')
    {
        return $this->_toOptionArray($value, $label);
    }

    /**
     * Add column with userId value.
     * Format: user_id.
     *
     * @return Xcom_Cse_Model_Resource_Channel_Collection
     */
    public function addUserIdTextField()
    {
        $this->getSelect()
             ->joinLeft(array('xaccount' => $this->getTable('xcom_cse/account')),
                 'main_table.account_id = xaccount.account_id');

        return $this;
    }

    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $countSelect = $this->_getCleanSelect();
        $countSelect->columns("count(DISTINCT main_table.channel_id)");
        return $countSelect;
    }

    /**
     * Get all ids for collection
     *
     * @return array
     */
    public function getAllIds()
    {
        $idsSelect = $this->_getCleanSelect();
        $idsSelect->columns('main_table.' . $this->getResource()->getIdFieldName());
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * @return Varien_Db_Select
     */
    protected function _getCleanSelect()
    {
        $select = clone $this->getSelect();
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::HAVING);
        $select->reset(Zend_Db_Select::GROUP);
        return $select;
    }
}
