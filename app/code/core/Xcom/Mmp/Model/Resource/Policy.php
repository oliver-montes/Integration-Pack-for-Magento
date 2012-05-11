<?php
/**
 *
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

class Xcom_Mmp_Model_Resource_Policy extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('xcom_mmp/policy', 'policy_id');
    }

    /**
     * Clean All Shipping Info for specific policy
     *
     * @param Xcom_Mmp_Model_Policy $object
     * @return Xcom_Mmp_Model_Resource_Policy
     */
    protected function _cleanPolicyShipping(Xcom_Mmp_Model_Policy $object)
    {
        $writeAdapter   = $this->_getWriteAdapter();
        $writeAdapter->delete(
            $this->getTable('xcom_mmp/policy_shipping'),
            $writeAdapter->quoteInto($this->getIdFieldName().'=?', $object->getId())
        );
        return $this;
    }

    /**
     * Save Shipping Info for specific policy
     *
     * @param Xcom_Mmp_Model_Policy $object
     * @return Xcom_Mmp_Model_Resource_Policy
     */
    public function savePolicyShipping(Xcom_Mmp_Model_Policy $object)
    {
        $this->_cleanPolicyShipping($object);

        $shippingData = $object->getShippingData();
        if ($shippingData) {
            foreach($shippingData AS $shippingId => $shippingDetails) {
                $bind   =   array(
                    'policy_id'     => $object->getId(),
                    'shipping_id'   => $shippingId,
                    'cost'          => $shippingDetails['cost'],
                    'sort_order'    => $shippingDetails['sort_order']);
                $this->_getWriteAdapter()->insert($this->getTable('xcom_mmp/policy_shipping'), $bind);
            }
        }
        return $this;
    }

    /**
     * Policy name validation. Must be unique inside channel
     *
     * @param string $name
     * @param int $channelId
     * @param int|null $policyId
     *
     * @return boolean
     */
    public function isPolicyNameUnique($name, $channelId, $policyId = null)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('count' => new Zend_Db_Expr('COUNT(policy_id)')))
            ->where($this->_getReadAdapter()->quoteIdentifier('name') . ' = ?', $name)
            ->where($this->_getReadAdapter()->quoteIdentifier('channel_id') . '= ?', $channelId);

        if ($policyId) {
            $select->where($this->_getReadAdapter()->quoteIdentifier('policy_id') . ' != ?', $policyId);
        }

        return $this->_getReadAdapter()->fetchOne($select) ? false : true;
    }
}
