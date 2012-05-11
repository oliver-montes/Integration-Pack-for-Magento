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

class Xcom_Mmp_Model_Resource_Account extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('xcom_mmp/account', 'account_id');
    }

    /**
     * Prepare select query.
     *
     * @param string $field
     * @param int $value
     * @param Varien_Object $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->joinLeft(array('xme' => Mage::getResourceModel('xcom_mmp/environment')->getMainTable()),
            $this->_getReadAdapter()->quoteIdentifier('xme.environment_id') . ' = ' .
                $this->_getReadAdapter()->quoteIdentifier($this->getMainTable() . '.environment'),
            array('environment_value' => 'xme.environment')
        );
        $select->columns(array(
            'validation_expired'  => $this->_getValidationExpiredExpr(),
        ));
        return $select;
    }

    /**
     * Returns database expression for validation_expired field.
     *
     * @return Zend_Db_Expr
     */
    protected function _getValidationExpiredExpr()
    {
        return new Zend_Db_Expr('(CASE WHEN DATEDIFF(validated_at, NOW()) < 0' .
            ' THEN 1 WHEN validated_at=0 THEN 1 ELSE 0 END)');
    }

    /**
     * Account validation. Must be unique pair (environment,user_id).
     *
     * @param $environment
     * @param $userId
     * @return string
     */
    public function getAccountIdByUniqueKey($environment, $userId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('environment=?', $environment)
            ->where('user_id=?', $userId);
        return $this->_getReadAdapter()->fetchOne($select);
    }

}
