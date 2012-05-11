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
class Xcom_Ebay_Model_Resource_ShippingService extends Xcom_Mmp_Model_Resource_ShippingService
{
    /**
     * @param Xcom_Mmp_Model_Channel $channel
     * @param Xcom_Mmp_Model_Policy $policy
     * @return array
     */
    public function getSortedShippingServices(
        Xcom_Mmp_Model_Channel $channel,
        Xcom_Mmp_Model_Policy $policy)
    {
        $select = $this->_getShippingServiceSelectSql($channel);
        $policyExpr = ' AND ' . $this->_getReadAdapter()
            ->quoteIdentifier('ps.policy_id') . ' = ' . (int) $policy->getId();

        $select->reset(Varien_Db_Select::ORDER);
        $select->joinLeft(array('ps' => $this->getTable('xcom_mmp/policy_shipping')),
            $this->getMainTable() . '.shipping_id = ' . $this->_getReadAdapter()->quoteIdentifier('ps.shipping_id')
            . $policyExpr,
            array('sort' => $this->_getSortOrderCaseExpr())
        )
        ->group($this->getMainTable() . '.shipping_id')
        ->order('sort ' . Varien_Db_Select::SQL_ASC)
        ->order('service_name ' . Varien_Db_Select::SQL_ASC);

        return $this->_getReadAdapter()->fetchAll($select);
    }

    /**
     * @return Zend_Db_Expr
     */
    protected function _getSortOrderCaseExpr()
    {
        return new Zend_Db_Expr(
            'CASE WHEN sort_order IS NULL THEN 10000 ELSE sort_order END'
        );
    }
}
