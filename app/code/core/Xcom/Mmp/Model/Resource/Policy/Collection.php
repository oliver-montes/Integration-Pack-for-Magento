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

class Xcom_Mmp_Model_Resource_Policy_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initialize resource model for collection
     *
     */
    public function _construct()
    {
        $this->_init('xcom_mmp/policy');
    }

    /**
     * Retrieve list of shipping methods assigned to policy
     *
     * @return Xcom_Mmp_Model_Resource_Policy_Collection
     */
    public function addShippingMethods()
    {
        $this->getSelect()
            ->joinLeft(array('ps' => $this->getTable('xcom_mmp/policy_shipping')),
                'ps.policy_id=main_table.policy_id')
            ->joinLeft(array('sh' => $this->getTable('xcom_mmp/shipping_service')),
                'ps.shipping_id=sh.shipping_id',
                array('shipping_description' => new Zend_Db_Expr(
                        'GROUP_CONCAT(sh.description ORDER BY `ps`.`sort_order` SEPARATOR \', \')'
                        ))
            )->group('ps.policy_id')
            ->order('ps.sort_order ASC');
        return $this;
    }

    /**
     * @param string $valueField
     * @param string $labelField
     * @return array
     */
    public function toOptionHash($valueField = 'policy_id', $labelField = 'name')
    {
        return $this->_toOptionHash($valueField, $labelField);
    }

    /**
     * @param string $valueField
     * @param string $labelField
     * @return array
     */
    public function toOptionArray($valueField = 'policy_id', $labelField = 'name')
    {
        return $this->_toOptionArray($valueField, $labelField);
    }
}

