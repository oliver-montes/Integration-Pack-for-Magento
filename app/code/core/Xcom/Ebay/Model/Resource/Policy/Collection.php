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

class Xcom_Ebay_Model_Resource_Policy_Collection extends Xcom_Mmp_Model_Resource_Policy_Collection
{
    /**
     * Add all additional data for every policy
     *
     * @return Xcom_Ebay_Model_Resource_Policy_Collection
     */
    public function addPolicyData()
    {
        $ids = array();
        foreach ($this as $policy) {
            $ids[]  = $policy->getId();
        }
        if (empty($ids)) {
            return $this;
        }

        $select = $this->getConnection()->select();
        $select->from($this->getTable('xcom_ebay/channel_policy'), '*')
            ->where('policy_id IN (?)', $ids);

        $policyData = array();
        foreach ($this->getConnection()->fetchAssoc($select) as $info) {
            $policyId = $info['policy_id'];
            unset($info['policy_ebay_id']);
            unset($info['policy_id']);
            $policyData[$policyId] = $info;

        }

        foreach ($this as $policy) {
            if (isset($policyData[$policy->getId()])) {
                $policy->addData($policyData[$policy->getId()]);
            }
        }

        return $this;
    }

    /**
     * @param array|string $status
     * @return Xcom_Ebay_Model_Resource_Policy_Collection
     */
    public function addStatusFilter($status)
    {
        if (is_array($status)) {
            $this->getSelect()->where('status IN (?)', $status);
        }
        elseif (is_string($status)) {
            $this->getSelect()->where('status = ?', $status);
        }
        return $this;
    }
}
