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
 * @package     Xcom_Ebay
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Ebay_Model_Resource_Policy extends Xcom_Mmp_Model_Resource_Policy
{
    /**
     * Policy config table name
     */
    protected $_policyEbayTable;

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_policyEbayTable = $this->getTable('xcom_ebay/channel_policy');
    }

    /**
     * Perform actions after object save
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Xcom_Mmp_Model_Resource_Policy
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $bind = $this->_prepareDataForTable($object, $this->_policyEbayTable);
        $bind['policy_id']  = $object->getId();
        $this->_getWriteAdapter()->insertOnDuplicate($this->_policyEbayTable, $bind, array_keys($bind));

        return parent::_afterSave($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param   string $field
     * @param   mixed $value
     * @param $object
     * @return  Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->joinLeft(array('ecp' => $this->_policyEbayTable),
                          'ecp.policy_id = '.$this->getMainTable() . '.policy_id');
        return $select;
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Mysql4_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getData('policy_id')) {
            return parent::_afterLoad($object);
        }
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('xcom_mmp/policy_shipping'))
            ->where($this->_getReadAdapter()->quoteIdentifier('policy_id') . ' = ?',
                (int)$object->getData('policy_id'));
        $object->setShippingData($this->_getReadAdapter()->fetchAll($select));

        return parent::_afterLoad($object);
    }
}
