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

class Xcom_Mmp_Model_Message_Marketplace_Environment_Search_Failed extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic = 'marketplace/environment/searchFailed';
        $this->_schemaRecordName = 'SearchEnvironmentFailed';
        parent::_construct();
    }

    /**
     * @return Xcom_Mmp_Model_Message_Marketplace_Environment_Search_Failed
     * @throw Mage_Core_Exception
     */
    public function process()
    {
        parent::process();
        $data = $this->getBody();
        if (!is_array($data) || !isset($data['errors']) || empty($data['errors']))
        {
            Mage::throwException('Message is not valid');
        }
        return $this;
    }
}
