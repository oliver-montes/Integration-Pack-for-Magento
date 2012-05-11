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
 * @package     Xcom_Chronicle
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Chronicle_Model_Message_Saleschannel_Sites_Inbound extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic               = 'salesChannel/site/search';
        $this->_schemaRecordName    = 'SearchSite';
        $this->_schemaVersion       = "1.0.0";

        parent::_construct();
    }

    /**
     * Process data on message received
     * @return Xcom_Chronicle_Model_Message_Saleschannel_Sites_Inbound
     */
    public function process()
    {
        parent::process();

        $channelName = Mage::getBaseUrl();
        try {
            if ($this->_validateSchema()) {
                $resultSet = $this->_processSearchSites();

                if (!empty($resultSet['results'])) {
                    $destinationId = Mage::getConfig()->getNode("default/xfabric/connection_settings/destination_id")->asArray();

                    $response = $this->_generateSuccessMessage($channelName,  $destinationId, $resultSet['results']);
                    Mage::helper('xcom_xfabric')->send('salesChannel/site/searchSucceeded', $response);
                }
                else {
                    $response = $this->_generateFailureMessage($channelName, $resultSet['error']);
                    Mage::helper('xcom_xfabric')->send('salesChannel/site/searchFailed', $response);
                }
            }
        }
        catch(Exception $ex) {
            Mage::logException($ex);
            $response = $this->_generateFailureMessage($channelName, $ex->getMessage());
            Mage::helper('xcom_xfabric')->send('salesChannel/site/searchFailed', $response);
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function _processSearchSites()
    {
        $results = null;
        $error = null;
        $allStores = Mage::app()->getStores();
        if (!empty($allStores)) {
            $results = array();
            foreach ($allStores as $store) {
                if ($store->getIsActive()) {
                    $results[] = $this->_buildSite($store);
                }
            }
            if (empty($results)) {
                // all stores are inactive
                $error = 'no sites available';
            }
        }
        else {
            $error = 'no sites available';
        }

        $resultSet = array(
            'results'  => $results,
            'error'    => $error,
        );

        return $resultSet;
    }

    /**
     * @param \Mage_Core_Model_Store $store
     * @return array
     */
    protected function _buildSite(Mage_Core_Model_Store $store)
    {
        $locale = preg_split('/_/', $store->getConfig('general/locale/code'));
        return array(
            'siteCode'  => $locale[1],
            'name'      => $store->getFrontendName() . '-' .$store->getName(),
            'siteUrl'   => $store->getUrl()
        );
    }

    /**
     * @param $channelName
     * @param $destinationId
     * @param array $sites
     * @return array
     */
    protected function _generateSuccessMessage($channelName, $destinationId, array $sites)
    {
       return array(
           'sites'         => $sites,
           'channelName'   => $channelName,
           'destinationId' => $destinationId,
           'destination_id' => $this->getPublisherPseudonym(),
       );
    }

    /**
     * @param $channelName
     * @param $message
     * @return array
     */
    protected function _generateFailureMessage($channelName, $message)
    {
        return array(
            'channelName' => $channelName,
            'errors' => array(
                array(
                    'code' => '-1',
                    'message' => $message,
                    'parameters' => null
                )
            ),
            'destination_id' => $this->getPublisherPseudonym(),
        );
    }
}
