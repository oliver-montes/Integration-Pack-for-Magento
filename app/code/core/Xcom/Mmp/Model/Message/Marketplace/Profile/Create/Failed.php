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
class Xcom_Mmp_Model_Message_Marketplace_Profile_Create_Failed extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic               = 'marketplace/profile/createFailed';
        $this->_schemaRecordName    = 'CreateProfileFailed';

        parent::_construct();
    }

    /**
     * Process data on message received
     * @return array
     */
    public function process()
    {
        $data = $this->getBody();
        if (!empty($data['errors'])) {
            $this->savePolicy();
            Mage::log($data, null, 'profileCreateFailed.log');
        }
        return parent::process();
    }

    public function savePolicy()
    {
        $correlationId = $this->getCorrelationId();
        if (!is_null($correlationId)) {
            $policy = Mage::getModel('xcom_mmp/policy')
                ->load($correlationId, 'correlation_id');
            $policy->setIsActive(0);
            $policy->setStatus(Xcom_Mmp_Model_Policy::XML_POLICY_STATUS_FAILED);
            $policy->save();
        }
        return $this;
    }
}
