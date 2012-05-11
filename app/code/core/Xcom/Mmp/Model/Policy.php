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

class Xcom_Mmp_Model_Policy extends Mage_Core_Model_Abstract
{
    const XML_POLICY_STATUS_PENDING    = 1;
    const XML_POLICY_STATUS_SUCCESS    = 2;
    const XML_POLICY_STATUS_FAILED     = 3;

    /**
     * Supported range of time periods, such as 3 days or 7 days
     *
     * @var array
     */
    protected $_returnPeriods   = array(3, 7, 14, 30, 60);

    /**
     * Internal constructor not depended on params. Can be used for object initialization
     */
    protected function _construct()
    {
        $this->_init('xcom_mmp/policy');
    }

    /**
     * Policy name validation
     *
     * @param string $name
     * @param int $channelId
     * @param int|null $policyId
     * @return bool
     */
    public function isPolicyNameUnique($name, $channelId, $policyId = null)
    {
        return $this->_getResource()->isPolicyNameUnique($name, $channelId, $policyId);
    }

    /**
     * Get supported range of returned periods.
     *
     * @return array
     */
    public function getReturnedPeriods()
    {
        return $this->_returnPeriods;
    }

    /**
     * Update policy shipping methods.
     *
     * The method will delete shipping methods for the given policy
     * and after that insert new ones if provided.
     *
     * @return Xcom_Mmp_Model_Policy
     */
    public function savePolicyShipping()
    {
        if ($this->getId()) {
            Mage::getResourceModel('xcom_mmp/policy')->savePolicyShipping($this);
        }
        return $this;
    }
}
