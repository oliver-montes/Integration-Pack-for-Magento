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

/**
 * Ebay Helper
 *
 * @category   Xcom
 * @package    Xcom_Ebay
 */

class Xcom_Ebay_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_attributeSets;

    /**
     * Path to channel type data.
     */
    const XML_PATH_XCOM_CHANNEL_TYPE_DATA                  = 'xcom/channel/type/ebay';

    /**
     * @var array
     */
    protected $_environmentHash;

    /**#@+
     * XML path to system configuration of eBay channel settings
     *
     * @see Methods which used these ones constants
     */
    const XML_PATH_XCOM_CHANNEL_ORDER_SYNC_EBAY_ACCOUNT   = 'xcom_channel/ebay/order_sync_ebay_account';
    const XML_PATH_XCOM_CHANNEL_ORDER_SYNC_START_DATE     = 'xcom_channel/ebay/order_sync_start_date';
    const XML_PATH_XCOM_CHANNEL_ORDER_SYNC_END_DATE       = 'xcom_channel/ebay/order_sync_end_date';
    const XML_PATH_XCOM_CHANNEL_ORDER_EXPORT_INCLUDE_CSV  = 'xcom_channel/ebay/order_export_include_channel_csv';
    const XML_PATH_XCOM_CHANNEL_INVENTORY_SYNC_INVENTORY_TO_ZERO
            = 'xcom_channel/ebay/inventory_sync_inventory_update_to_zero';
    const XML_PATH_XCOM_CHANNEL_INVENTORY_SYNC_PRODUCT_OUT_OF_STOCK
            = 'xcom_channel/ebay/inventory_sync_out_stock_update_to_zero';
    const XML_PATH_XCOM_CHANNEL_REGISTRATION_TARGET_CAPABILITY_NAME
        = 'xcom_channel/ebay/registration/target_capability_name';
    const XML_PATH_XCOM_CHANNEL_REGISTRATION_STORE_ENDPOINT_URL
        = 'xcom_channel/ebay/registration/store_endpoint_url';
    const XML_PATH_XCOM_CHANNEL_REGISTRATION_IS_REGISTERED
        = 'xcom_channel/ebay/registration/is_registered';
    const XML_PATH_XCOM_CHANNEL_REGISTRATION_EXTENSION_ENABLED
        = 'xcom_channel/ebay/registration/extension_enabled';
    const XML_PATH_XCOM_CHANNEL_REGISTRATION_REQUEST_URL
        = 'xcom_channel/ebay/registration/request_url';
    const XML_PATH_XCOM_CHANNEL_REGISTRATION_LEGAL_AGREEMENT_URL
        = 'xcom_channel/ebay/registration/legal_agreement_url';
    const XML_PATH_XCOM_CHANNEL_REGISTRATION_STORE_FRONT_PLATFORM
        = 'xcom_channel/ebay/registration/store-front-platform';
    /**#@-*/

    /**
     * Retrieve valid date of eBay authorization.
     *
     * @return string
     */
    public function getAuthorizationValidDate()
    {
        return date('F d, Y');  // TODO:
    }

    /**
     * Retrieve channeltype configuration data.
     *
     * @return mixed
     */
    public function getChanneltypeData()
    {
        return Mage::getStoreConfig(self::XML_PATH_XCOM_CHANNEL_TYPE_DATA);
    }

    /**
     * Retrieve channeltype code.
     *
     * @return string
     */
    public function getChanneltypeCode()
    {
        $data = $this->getChanneltypeData();
        return $data['code'];
    }

    /**
     * @return array
     */
    public function prepareAttributeSetArray()
    {
        if (null === $this->_attributeSets) {
            $this->_attributeSets = array();
        }
        if (empty($this->_attributeSets)) {
            $productIds = $this->getRequestProductIds();
            /** @var $product Xcom_Listing_Model_Channel_Product */
            $product = Mage::getModel('xcom_listing/channel_product');
            $this->_attributeSets = $product->getProductAttributeSets($productIds);
        }

        return $this->_attributeSets;
    }

    /**
     * @return array
     */
    public function getRequestProductIds($productIds = array())
    {
        if (empty($productIds)) {
            //TODO InfieldDesign:change depending on outcome of channel product listing/* message requirements
            $productIds = Mage::app()->getRequest()->getParam('selected_products');
        }
        if (!is_array($productIds) && false !== strpos($productIds, ',')) {
            $productIds = explode(',', $productIds);
        }
        if (!empty($productIds)) {
            $this->getSession()->setChannelProductIds($productIds);
        } elseif ($this->getSession()->getChannelProductIds()) {
            $productIds = $this->getSession()->getChannelProductIds();
        }
        if (!is_array($productIds)) {
            return array();
        }
        return $productIds;
    }

    /**
     * @return Mage_Adminhtml_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * Check whether selected products have unmapped attributeSets and unmapped required attributes
     *
     * @return bool
     */
    public function validateIsRequiredAttributeHasMappedValue()
    {
        $attributeSetArray = $this->prepareAttributeSetArray();
        foreach($attributeSetArray as $attributeSetId) {
            /** @var $resourceProductType Xcom_Mapping_Model_Resource_Product_Type */
            $resourceProductType = Mage::getResourceModel('xcom_mapping/product_type');
            $mappingProductTypeId = $resourceProductType->getMappingProductTypeId($attributeSetId);

            if($mappingProductTypeId === false) {
                return true;
            }

            /** @var $validator Xcom_Mapping_Model_Validator */
            $validator = Mage::getModel('xcom_mapping/validator');
            $allValuesMapped = $validator->validateIsRequiredAttributeHasMappedValue(
                $mappingProductTypeId,
                null,
                $attributeSetId);

            if (!$allValuesMapped) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get system config Order Synchronization eBay Account
     *
     * @return string|null
     */
    public function getOrderSynchronizationEbayAccount()
    {
        return Mage::getStoreConfig(self::XML_PATH_XCOM_CHANNEL_ORDER_SYNC_EBAY_ACCOUNT);
    }

    /**
     * Get system config Order Synchronization Start Date
     *
     * @return string|null
     */
    public function getOrderSynchronizationStartDate()
    {
        return Mage::getStoreConfig(self::XML_PATH_XCOM_CHANNEL_ORDER_SYNC_START_DATE);
    }

    /**
     * Get system config Order Synchronization End Date
     *
     * @return string|null
     */
    public function getOrderSynchronizationEndDate()
    {
        return Mage::getStoreConfig(self::XML_PATH_XCOM_CHANNEL_ORDER_SYNC_END_DATE);
    }

    /**
     * Is  include Channel Type in CSV Order Export
     *
     * @return bool
     */
    public function isOrderExportIncludeCsv()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_XCOM_CHANNEL_ORDER_EXPORT_INCLUDE_CSV);
    }

    /**
     * Is update Channel Listing to Zero(0) when Magento Inventory turns to Zero(0)?
     *
     * @return bool
     */
    public function isUpdateChannelToZeroOnInventoryToZero()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_XCOM_CHANNEL_INVENTORY_SYNC_INVENTORY_TO_ZERO);
    }

    /**
     * Is update Channel Listing to Zero(0) when Magento product changes to "Out of Stock"?
     *
     * @return bool
     */
    public function isUpdateChannelToZeroOnProductOutOfStock()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_XCOM_CHANNEL_INVENTORY_SYNC_PRODUCT_OUT_OF_STOCK);
    }

    /**
     * Update existed listings if qty of some products was changes to 0 or product has out of stock status
     *
     * @param array $productIds
     */
    public function updateListingForProduct(array $productIds)
    {
        $listingIds = Mage::getModel('xcom_listing/channel_product')->getPublishedListingIds($productIds);
        foreach ($listingIds as $listingId => $listingData) {
            /** @var $listing Xcom_Core_Model_Listing */
            $listing = Mage::getModel('xcom_listing/listing');
            $listing->load($listingId);
            //listing has been already sent with qty = 0
            if ($listing->getQtyValue() == 0) {
                continue;
            }
            $listing->setQtyValue(0);
            $listing->setQtyValueType('abs');
            $listing->setChannelId($listingData['channel_id']);
            $listing->prepareProducts($listingData['product_ids']);

            $options = array(
                'policy'  => Mage::getModel('xcom_ebay/policy')->load($listing->getPolicyId()),
                'channel' => Mage::getModel('xcom_ebay/channel')->load($listingData['channel_id'])
            );
            $listing->send($options);
            $listing->save();
            $listing->saveProducts();
        }
    }

    /**
     * Check Ebay extension enabled status
     *
     * @return bool
     */
    public function isExtensionEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_XCOM_CHANNEL_REGISTRATION_EXTENSION_ENABLED);
    }

    /**
     * Check xFabric registration status
     * @return bool
     */
    public function isXfabricRegistered()
    {
        return (bool)Mage::helper('xcom_xfabric')->getResponseAuthorizationKey();
    }

    /**
     * Retrieve registration target capability name
     *
     * @return string
     */
    public function getRegistrationTargetCapabilityName()
    {
        return Mage::getStoreConfig(self::XML_PATH_XCOM_CHANNEL_REGISTRATION_TARGET_CAPABILITY_NAME);
    }

    /**
     * Retrieve registration store endpoint url
     *
     * @return string
     */
    public function getRegistrationStoreEndpointUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_XCOM_CHANNEL_REGISTRATION_STORE_ENDPOINT_URL);
    }

    /**
     * Retrieve registration extension status param
     *
     * @return bool
     */
    public function getRegistrationIsRegistered()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_XCOM_CHANNEL_REGISTRATION_IS_REGISTERED);
    }

    /**
     * Retrieve registration request url
     *
     * @return string
     */
    public function getRegistrationRequestUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_XCOM_CHANNEL_REGISTRATION_REQUEST_URL);
    }

    /**
     * Retrieve registration legal agreement url
     *
     * @return string
     */
    public function getRegistrationLegalAgreementUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_XCOM_CHANNEL_REGISTRATION_LEGAL_AGREEMENT_URL);
    }

    /**
     * Retrieve registration srore front platform
     *
     * @return string
     */
    public function getRegistrationStoreFrontPlatform()
    {
        return Mage::getStoreConfig(self::XML_PATH_XCOM_CHANNEL_REGISTRATION_STORE_FRONT_PLATFORM);
    }

    /**
     * Retrieve registration request
     *
     * @return Varien_Object
     */
    public function getRegistrationRequest()
    {
        $request = new Varien_Object();
        $postData = array(
            'target_capability_name' => $this->getRegistrationTargetCapabilityName(),
            'store_endpoint_url'     => $this->getRegistrationStoreEndpointUrl(),
            'is_registered'          => $this->getRegistrationIsRegistered(),
            'legal_agreement_url'    => $this->getRegistrationLegalAgreementUrl(),
            'store-front-platform'   => $this->getRegistrationStoreFrontPlatform()
        );
        $jsonData = Zend_Json::encode($postData);
        $request->setOnboardingInfo(urlencode($jsonData));
        return $request;
    }
    
    /**
     * @return array
     */
    public function getEnvironmentHash()
    {
        if (null === $this->_environmentHash) {
            $this->_environmentHash = Mage::getModel('xcom_mmp/environment')->getCollection()
                ->addFieldToFilter('channel_type_code', 'eBay')
                ->addFieldToFilter('site_code', 'US')
                ->toOptionHash();
        }
        return $this->_environmentHash;
    }

}
