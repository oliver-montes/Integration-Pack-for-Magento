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

class Xcom_Mmp_Model_Message_Marketplace_Site_Search_Succeeded extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_schemaRecordName = 'SearchSiteSucceeded';
        $this->_topic = 'marketplace/site/searchSucceeded';

        parent::_construct();
    }

    /**
     * Process data on message received.
     *
     * @return Xcom_Mmp_Model_Message_Marketplace_Site_Search_Succeeded
     */
    public function process()
    {
        if ($this->_validateSchema()) {
            $this->_saveSites($this->getBody());
        }
        return parent::process();
    }

    /**
     * Save/update site data to DB.
     *
     * @param array $data
     * @return Xcom_Mmp_Model_Message_Marketplace_Site_Search_Succeeded
     */
    protected function _saveSites($data = array())
    {
        if (!empty($data['sites'])) {
            $siteData = array();
            foreach ($data['sites'] as $site) {
                $siteData[]   = array(
                    'name'              => !empty($site['name']) ? $site['name'] : null,
                    'site_code'   => !empty($site['siteCode'])? $site['siteCode'] : null,
                );
                $this->dependedMessageData['siteCodes'][] = $site['siteCode'];
            }
            Mage::getResourceModel('xcom_mmp/site')
                ->upgrade($data['marketplace'], $siteData);
        }
        return $this;
    }

    /**
     * Prepare site response data.
     *
     * @param array $data
     * @return array
     */
    protected function _prepareResponseData(&$data)
    {
        return !empty($data['sites']) ? $data['sites'] : array();
    }
}
