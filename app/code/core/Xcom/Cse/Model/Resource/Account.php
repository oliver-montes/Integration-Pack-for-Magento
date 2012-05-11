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


class Xcom_Cse_Model_Resource_Account extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('xcom_cse/account', 'account_id');
    }

    /**
     * Retrieve select object for load object data.
     *
     * @param $field
     * @param $value
     * @param $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $readAdapter = $this->_getReadAdapter();

        $select = $readAdapter->select()
            ->from($this->getMainTable())
            ->where($this->getMainTable().'.'.$field.'=?', $value);
        return $select;
    }

    /**
     * Account validation. User_id must be present.
     *
     * @param $user_id
     * @return string
     */
    public function getAccountIdByUniqueKey($user_id)
    {
        $readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select()
            ->from($this->getMainTable())
            ->where('user_id=?', $user_id);
        return $this->_getReadAdapter()->fetchOne($select);
    }
    
    /**
     * Returns the active account.
     *
     * @return string
     */
//    public function getActiveAccount()
//    {
//        $readAdapter = $this->_getReadAdapter();
//        $select = $readAdapter->select()
//            ->from($this->getMainTable())
//            ->where('user_id=?', $user_id);
//        return $this->_getReadAdapter()->fetchOne($select);
//    }
}
