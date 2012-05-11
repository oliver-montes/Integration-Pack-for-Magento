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
 * @package     Xcom_Listing
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Listing_Model_Message_Listing_Update_Request extends Xcom_Listing_Model_Message_Listing_Create_Request
{
    protected function _construct()
    {
        parent::_construct();
        $this->_topic = 'listing/update';
        $this->_schemaRecordName = 'UpdateListing';
        $this->_action = 'update';
    }

    /**
     * Prepare data before sending
     *
     * @param $dataObject Varien_Object
     * @return Xcom_Listing_Model_Message_Listing_Update_Request
     */
    public function _prepareData(Varien_Object $dataObject = null)
    {
        $this->_validateOptions($dataObject);
        $policy = $dataObject->getPolicy();
        $xProfileId = '';
        if (is_object($policy) && $policy->getId()) {
            $this->_setPolicy($policy);
            $xProfileId = (string)$policy->getXprofileId();
        }

        $data = array(
            'updates' => $this->_prepareUpdatesRecord($dataObject->getData('products')),
            'xProfileId' => $xProfileId
        );

        $this->setMessageData($data);
        $logRequest = $this->_saveLogRequestBody();
        $this->setCorrelationId($logRequest->getCorrelationId());
        $this->_prepareChannelHistory($logRequest, $dataObject);


        $this->_hasDataChanges = true;
        $this->addHeader(
            Xcom_Xfabric_Model_Message_Abstract::CORRELATION_ID_HEADER,
            $logRequest->getCorrelationId()
        );
        return $this;
    }

    /**
     * @param Varien_Object|null $options
     * @throws Mage_Core_Exception
     * @return Xcom_Listing_Model_Message_Listing_Update_Request
     */
    protected function _validateOptions($options)
    {
        if (!is_object($options)) {
            $this->_throwException('Options should be specified.');
        }

        if (!is_object($options->getChannel())) {
            $this->_throwException('Channel should be specified.');
        }

        if (!is_array($options->getProducts())) {
            $this->_throwException('Products should be specified.');
        }
        return $this;
    }

    protected function _prepareUpdatesRecord(array $products)
    {
        $allListings = array();
        foreach ($products as $product) {
            if (is_object($product) && $product->getId()) {
                $listing = $this->_prepareListingInformation($product);
                $listing['marketItemId'] = $product->getListingMarketItemId();
                $allListings[] = $listing;
            }
        }
        return $allListings;
    }

    /**
     * @return null
     */
    protected function _prepareReturnPolicy()
    {
        return null;
    }

    /**
     * @param Varien_Object $product
     * @return mixed
     */
    protected function _prepareEmbeddedMessage(Varien_Object $product)
    {
        return null;
    }
}
