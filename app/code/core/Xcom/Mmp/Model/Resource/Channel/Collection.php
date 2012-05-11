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

class Xcom_Mmp_Model_Resource_Channel_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initialize resource model for collection
     * @return void
     */
    public function _construct()
    {
        $this->_init('xcom_mmp/channel');
    }

    /**
     * Add filter to select by code.
     *
     * @param string $channelTypeCode
     * @return Xcom_Mmp_Model_Resource_Channel_Collection
     */
    public function addChanneltypeCodeFilter($channelTypeCode)
    {
        return $this->addFieldToFilter('main_table.channeltype_code', $channelTypeCode);
    }

    /**
     * @return Xcom_Mmp_Model_Resource_Channel_Collection
     */
    public function addActiveChannelAndPolicyFilter()
    {
        $this->getSelect()
            ->where('main_table.is_active = 1')
            ->where('EXISTS('.
                $this->getConnection()->select()
                    ->from(array('chp' => $this->getTable('xcom_mmp/policy')), array('policy_id'))
                    ->where('main_table.channel_id = chp.channel_id')
                    ->where('chp.is_active = 1')
                    ->where('chp.xprofile_id IS NOT NULL')
            .')');
        return $this;
    }

    /**
     * Join core_store table with main_table.
     * Add website_id and group_id fields.
     *
     * @return Xcom_Mmp_Model_Resource_Channel_Collection
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
     * Add column with policy names for every channel
     *
     * @return Xcom_Mmp_Model_Resource_Channel_Collection
     */
    public function addPolicyNames()
    {
        $allowedStatus = array(
            Xcom_Mmp_Model_Policy::XML_POLICY_STATUS_PENDING,
            Xcom_Mmp_Model_Policy::XML_POLICY_STATUS_SUCCESS
        );
        $this->getSelect()
            ->joinLeft(array('xcp' => $this->getTable('xcom_mmp/policy')),
                    'main_table.channel_id = xcp.channel_id AND xcp.status IN(' . join(",", $allowedStatus) . ')',
                array('policy_name'   => new Zend_Db_Expr('GROUP_CONCAT(xcp.name SEPARATOR \', \')')))
            ->group('main_table.channel_id');
        return $this;
    }

    /**
     * Add column with userId value.
     * Format: user_id (environment).
     * @return Xcom_Mmp_Model_Resource_Channel_Collection
     */
    public function addUserIdTextField()
    {
        $this->getSelect()
            ->joinLeft(array('xaccount' => Mage::getResourceModel('xcom_mmp/account')->getMainTable()),
                $this->getConnection()->quoteIdentifier('main_table.account_id')
                    . ' = ' . $this->getConnection()->quoteIdentifier('xaccount.account_id'),
                array()
            )
            ->joinLeft(array('xme' => Mage::getResourceModel('xcom_mmp/environment')->getMainTable()),
                $this->getConnection()->quoteIdentifier('xme.environment_id') . ' = ' .
                    $this->getConnection()->quoteIdentifier('xaccount.environment'),
                array()
            );
        $this->getSelect()->columns(array(
            'marketplace' => new Zend_Db_Expr("CONCAT(user_id,' (',xme.environment,')')")));

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
