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
 * @package     Xcom_Google
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Helper
 *
 * @category   Xcom
 * @package    Xcom_Google
 */

class Xcom_Google_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Path to channel type data.
     */
    const XML_PATH_XCOM_CHANNELTYPE_DATA = 'xcom/channel/type/google';

    /**#@+
     * XML path to system configuration of Google channel settings
     *
     * @see Methods which used these ones constants
     */    
    const XML_PATH_XCOM_CHANNEL_REGISTRATION_TARGET_CAPABILITY_NAME
        = 'xcom_channel/google/registration/target_capability_name';
    const XML_PATH_XCOM_CHANNEL_REGISTRATION_STORE_ENDPOINT_URL
        = 'xcom_channel/google/registration/store_endpoint_url';
    const XML_PATH_XCOM_CHANNEL_REGISTRATION_IS_REGISTERED
        = 'xcom_channel/google/registration/is_registered';
    const XML_PATH_XCOM_CHANNEL_REGISTRATION_EXTENSION_ENABLED
        = 'xcom_channel/google/registration/extension_enabled';
    const XML_PATH_XCOM_CHANNEL_REGISTRATION_REQUEST_URL
        = 'xcom_channel/google/registration/request_url';
    const XML_PATH_XCOM_CHANNEL_REGISTRATION_LEGAL_AGREEMENT_URL
        = 'xcom_channel/google/registration/legal_agreement_url';
    const XML_PATH_XCOM_CHANNEL_REGISTRATION_STORE_FRONT_PLATFORM
        = 'xcom_channel/google/registration/store-front-platform';
    /**#@-*/
        
    /**
     * Retrieve channeltype configuration data.
     *
     * @return mixed
     */
    public function getChanneltypeData()
    {
        return Mage::getStoreConfig(self::XML_PATH_XCOM_CHANNELTYPE_DATA);
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
     * Check Google extension enabled status
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
}
