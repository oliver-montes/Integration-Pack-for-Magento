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

class Xcom_Mmp_Model_Message_Marketplace_Country_Search_Succeeded extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic = 'marketplace/country/searchSucceeded';
        $this->_schemaRecordName = 'SearchCountrySucceeded';
        parent::_construct();
    }

    /**
     * Process data on message received
     * @return Xcom_Mmp_Model_Message_Marketplace_Country_Find_Response
     */
    public function process()
    {
        parent::process();
        $data = $this->getBody();

        if ($this->_validateSchema()) {
            $this->_saveCountries($data);
        }
        return $this;
    }

    /**
     * Insert/update country data to DB
     *
     * @param array $data
     * @return Xcom_Mmp_Model_Message_Marketplace_Country_Find_Response
     */
    protected function _saveCountries($data)
    {
        /** @var $model Xcom_Mmp_Model_Resource_Country */
        $model              = Mage::getResourceModel('xcom_mmp/country');
        $channelTypeCode    = $data['marketplace'];
        $siteCode           = $data['siteCode'];
        $environment        = $data['environmentName'];
        $countryData        = array();
        foreach ($data['countries'] as $country) {
            $countryData[]           = array(
                'country_code'      => !empty($country['countryCode']) ? $country['countryCode'] : null,
                'description'       => !empty($country['description']) ? $country['description'] : null,
            );
        }
        $model->upgrade($channelTypeCode, $siteCode, $environment, $countryData);
        return $this;
    }

    /**
     * Prepare country response data.
     *
     * @param array $data
     * @return array
     */
    protected function _prepareResponseData(&$data)
    {
        $countries   = !empty($data['countries']) ? $data['countries'] : array();
        return $countries;
    }
}
