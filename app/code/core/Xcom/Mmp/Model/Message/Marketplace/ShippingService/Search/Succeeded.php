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

class Xcom_Mmp_Model_Message_Marketplace_ShippingService_Search_Succeeded extends Xcom_Xfabric_Model_Message_Response
{

    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic               = 'marketplace/shippingService/searchSucceeded';
        $this->_schemaRecordName    = 'SearchShippingServiceSucceeded';
        parent::_construct();
    }

    /**
     * Process data on message received
     * @return Xcom_Mmp_Model_Message_Marketplace_ShippingService_Search_Succeeded
     */
    public function process()
    {
        parent::process();
        $data = $this->getBody();

        if ($this->_validateSchema()) {
            $this->_saveShippingServices($data);
        }
        return $this;
    }

    /**
     * Insert/update shipping service data to DB
     *
     * @param array $data
     * @return Xcom_Mmp_Model_Message_Marketplace_ShippingService_Search_Succeeded
     */
    protected function _saveShippingServices($data)
    {
        /** @var $model Xcom_Mmp_Model_Resource_ShippingService */
        $model              = Mage::getResourceModel('xcom_mmp/shippingService');
        $channelTypeCode    = $data['marketplace'];
        $siteCode           = $data['siteCode'];
        $environment        = $data['environmentName'];
        $shippingData        = array();
        foreach ($data['services'] as $service) {
            $shippingData[] = $this->_prepareShippingMethodParams($service);
        }
        $model->upgrade($channelTypeCode, $siteCode, $environment, $shippingData);
        return $this;
    }

    /**
     * Prepare shipping response data.
     *
     * @param array $data
     * @return array
     */
    protected function _prepareResponseData(&$data)
    {
        if (!isset($data['services'])) {
            return array();
        }
        return $this->_prepareShippingMethods($data['services']);
    }

    /**
     * Use _prepareShippingMethodParams for every shipping service element
     *
     * @param array $shippingMethods
     * @return array
     */
    protected function _prepareShippingMethods(array $shippingMethods)
    {
        $shippingData = array();
        foreach ($shippingMethods AS $shippingMethod) {
            $shippingData[] = $this->_prepareShippingMethodParams($shippingMethod);
        }
        return $shippingData;
    }

    /**
     * Prepare shipping service data format where name of element can be user as name of DB field
     * and value of every element must be a string
     *
     * @param array $shippingMethod
     * @return array
     */
    protected function _prepareShippingMethodParams($shippingMethod)
    {
        $methodParams = array();
        foreach($shippingMethod as $key => $value) {
            //every value of Shipping service must be a string
            if (is_array($value)) {
                $value = implode(",", $value);
            }
            //Converts field names for setters and getters
            $methodParams[$this->_underscore($key)] = $value;
        }

        return $methodParams;
    }
}
