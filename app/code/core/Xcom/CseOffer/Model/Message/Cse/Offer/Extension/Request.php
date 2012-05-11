
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
 * @package     Xcom_CseOffer
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_CseOffer_Model_Message_Cse_Offer_Extension_Request extends Xcom_Xfabric_Model_Message_Request
{
	const ATTRIBUTE_GTIN = 'gtin';
	const AVAILABILITY_TYPE_INSTOCK = 'InStock';
	const IMAGE_BACKGROUND_TYPE_UNKNOWN = "Unknown";
	const IMAGE_PURPOSE_GALLERY = 'Gallery';
	const IMAGE_PURPOSE_THUMBNAIL = 'Thumbnail';
	
	/**
     * Schema version of the message (via X-XC-SCHEMA-VERSION)
     * @var string
     */
    protected $_schemaVersion = '3.0.0';
    	
    protected function _construct()
    {
        parent::_construct();
        $this->_schemaRecordName = 'CseOffer';
        $this->_topic      = 'com.x.cse.v0/CseOffer';
    }

    /**
     * Prepare data before sending
     *
     * @param Varien_Object $dataObject
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    public function _prepareData(Varien_Object $dataObject = null)
    {
    	$product = $dataObject->getData('product');
    	$store = Mage::app()->getStore($product->getData('store_id'));
        $mappingData = $this->getMappingOptions($product);
        $this->setMessageData(array(
        	'title'				=> $product->getData('name'),
	       	'description'		=> $product->getData('description'),
        	'manufacturer'		=> $product->getData('manufacturer'),
        	'mpn'				=> $product->getData('mpn'),
        	'gtin'				=> $this->_getGtin($mappingData),
        	'brand'				=> $product->getData('brand'),
        	'category'			=> null,  // Google adapter will discover category based on taxonomy xProductTypeId
			'xProductTypeId'	=> $this->_getProductTypeId($product),
        	'productTypes'		=> null,
			'images'			=> $this->_getImageUrls($product),
        	'link'				=> $product->getProductUrl(),
        	'originalPrice'		=> $this->_createCurrencyAmount($store, $product->getData('price')),
        	'availability'		=> self::AVAILABILITY_TYPE_INSTOCK,
        	'taxRate'			=> null,
        	'shipping'			=> null,
        	'shippingWeight'	=> (double)$product->getData('weight'),
        	'attributes'		=> $this->_createAttributes($mappingData),
			'variations'		=> null,
        	'offerId'			=> null,
        	'cpc'				=> $this->_createCurrencyAmount($store, 0)
        ));
        return parent::_prepareData($dataObject);
    }
    
    /**
     * Create attributes from mapped attributes.
     * @return array
     */
    protected function _createAttributes($mappingData)
    {
        $result = array();
    	if ($mappingData) {        
	        foreach ($mappingData as $targetAttributeName => $targetAttributeValue) {
	        	if ($targetAttributeName != self::ATTRIBUTE_GTIN) {
		            if (is_array($targetAttributeValue)) {
		            	// Cse contract only supports a single attribute value.
		                $targetAttributeValue = $targetAttributeValue[0];
		            }
		            $result[] = array(
		            	'id' => $product->getId(),
		                'name' => $targetAttributeName,
		                'value' => $targetAttributeValue
		            );
	        	}
	        }
    	}
        return $result;
    }
    
    /**
     * @param $amount
     * @return array
     */
    protected function _createCurrencyAmount($store, $amount)
    {
        return array(
            'amount'    => (string)$amount,
            'code'      => $store->getCurrentCurrencyCode()
        );
    }
    
    protected function _getGtin($mappingData) {
    	$result = null;
    	if ($mappingData) {
	    	foreach ($mappingData as $targetAttributeName => $targetAttributeValue) {
	    		if ($targetAttributeName == self::ATTRIBUTE_GTIN) {
	    			$result = $targetAttributeValue;
	    			break;
	    		}
	    	}
    	}
    	return $result;
    }
    
    /**
     * Retrieve xProductTypeId for this product
     *
     * @param Varien_Object $product
     * @return int|null
     */
    protected function _getProductTypeId(Varien_Object $product)
    {
        $productTypeId      = Mage::getResourceModel('xcom_mapping/product_type')
                ->getProductTypeId($product->getAttributeSetId());
        return $productTypeId ? $productTypeId : null;
    }
    
    /**
     * Prepare image url for product.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array|null
     */
    protected function _getImageUrls(Mage_Catalog_Model_Product $product)
    {
        $imageCollection    = array();
        //base image
        $image = $product->getData('image');
        if ($image && $image !='no_selection') {
            $imageUrl  = (string)Mage::helper('catalog/image')->init($product, 'image');
            $imageCollection[]  = $this->_getImageUrlOption($imageUrl, self::IMAGE_PURPOSE_THUMBNAIL);
        }
        //gallery
        $mediaGallery       = $product->getMediaGalleryImages();
        foreach ($mediaGallery as $image) {
            $imageCollection[]  = $this->_getImageUrlOption($image->getUrl(), self::IMAGE_PURPOSE_GALLERY);
        }
        return $imageCollection ? $imageCollection : null;
    }

    /**
     * Get data in ProductImage format
     *
     * @param $imageUrl
     * @return array
     */
    protected function _getImageUrlOption($imageUrl, $imagePurpose)
    {
        return array(
        	'imageUrl' => $imageUrl,
			'imagePurpose' => $imagePurpose,
			'backgroundType' => self::IMAGE_BACKGROUND_TYPE_UNKNOWN
		);
    }
}
