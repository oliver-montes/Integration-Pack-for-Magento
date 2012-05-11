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

class Xcom_Chronicle_Model_Message_Product extends Varien_Object
{
    protected $_locale;
    protected $_currency;

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function __construct(Mage_Catalog_Model_Product $product)
    {

        // Get the locale of this product.  Should probably do this for all stores
        $locale = $product->getStore()->getConfig('general/locale/code');

        $this->_locale = preg_split('/_/', $locale);
        $this->_currency = Mage::app()->getBaseCurrencyCode();
        $this->setData($this->_createProduct($product));
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _createProduct(Mage_Catalog_Model_Product $product)
    {
        $brand = null;
        if ($product->hasData('brand')) {
            $brand = $product->getAttributeText('brand');
        }
        $manufacturer = null;
        if ($product->hasData('manufacturer')) {
           $manufacturer = $product->getAttributeText('manufacturer');
        }


        $data = array(
            'id'                => $product->getEntityId(),
            'productTypeId'     => $this->_getProductTypeId($product),
            'name'              => array($this->_createLocalizedValue($product->getName())),
            'shortDescription'  => array($this->_createLocalizedValue($product->getShortDescription())),
            'description'       => array($this->_createLocalizedValue($product->getDescription())),
            'GTIN'              => $product->hasData('gtin') ?
                $product->getAttributeText('gtin') : null,
            'brand'             => !empty($brand) ?
                array($this->_createLocalizedValue($brand)) : null,
            'manufacturer'      => !empty($manufacturer) ?
                array($this->_createLocalizedValue($manufacturer)) : null,
            'MPN'               => $product->hasData('mpn') ?
                $product->getAttributeText('mpn') : null,
            'MSRP'              => $this->_getMsrp($product),
            'MAP'               => $product->hasData('map') ?
                $this->_getCurrencyAmount($product->getAttributeText('map')) : null,
            'images'            => $this->_createImageUrls($product),
            'attributes'        => $this->_createAttributes($product),
            'variationFactors'  => null,
            'skuList'           => $this->_createSKU($product)
        );

        return $data;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _getMsrp($product)
    {
        if ($product->hasData('msrp')) {
            if ($product->getAttributeText('msrp')) {
                return $this->_getCurrencyAmount($product->getAttributeText('msrp'));
            }
            $msrpData = $product->getData('msrp');
            return $this->_getCurrencyAmount($msrpData);
        }
        return null;
    }

    /**
     * @param $amount
     * @return array
     */
    protected function _getCurrencyAmount($amount)
    {
        return array(
            'amount'    => $amount,
            'code'      => $this->_currency,
        );
    }

    /**
     * @param Varien_Object $product
     * @return null
     */
    protected function _getProductTypeId(Varien_Object $product)
    {
        $productTypeId = Mage::getResourceModel('xcom_mapping/product_type')
            ->getProductTypeId($product->getAttributeSetId());
        return $productTypeId ? $productTypeId : null;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _createAttributes(Mage_Catalog_Model_Product $product)
    {
        $result = array();
        /** @var $mapper Xcom_Mapping_Model_Mapper */
        $mappingOptions = Mage::getSingleton('xcom_mapping/mapper')
            ->getMappingOptions($product);

        foreach ($mappingOptions as $key => $value) {
            $value = array(
                'attributeId'       => $key,
                //for now hard code it to ProductTypeString, need to figure out about StringEnumerationAttributeValue
                // or BooleanAttributeValue
                'attributeValue'    => $this->_createProductTypeAttributeValue($key, $value, 'string'),
            );
            $result[] = $value;
        }

        $mappedAttributeCodes = array();
        $mappingData = Mage::getModel('xcom_mapping/attribute')
            ->getSelectAttributesMapping($product->getAttributeSetId());
        foreach ($mappingData as $mappedData) {
            $mappedAttributeCodes[] = $mappedData['attribute_code'];
        }

        $attributes =  $product->getAttributes();

        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attributeType = null;
        $attributeValue = null;
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if ($attribute->getIsUserDefined() && $attribute->getFrontendInput() == 'select') {
                $attributeValue = $product->getAttributeText($attributeCode);
                $attributeType = 'string';
            }
            else {
                $attributeValue = $product->getData($attributeCode);
                $attributeType = $attribute->getFrontendInput() == 'boolean' ? 'boolean' : 'string';
            }

            if ($attribute->getIsUserDefined() && !empty($attributeValue) &&
                !in_array($attributeCode, $mappedAttributeCodes)) {
                $value = array(
                    'attributeId'       => $attribute->getName(),
                    'attributeValue'    => $this->_createCustomAttributeValue(
                            $attributeCode, $attributeValue, $attributeType),
                );
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _createSKU(Mage_Catalog_Model_Product $product)
    {
        $sku = $product->getSku();
        if(!isset($sku)) {
            return array();
        }
        $data =
            array(
                array(
                    'sku'                       => $product->getSku(),
                    'productId'                 => null,
                    'MSRP'                      => null,
                    'MAP'                       => null,
                    'variationAttributeValues'  => null,
                    'images'                    => null
                )
            );

        return $data;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array|null
     */
    protected function _createImageUrls(Mage_Catalog_Model_Product $product)
    {
        $image = $product->getData('image');
        if (empty($image) || $image =='no_selection') {
            return null;
        }

        $imageCollection = array();
        $imageUrl = (string)Mage::helper('catalog/image')->init($product, 'image');
        $imageCollection[] = $this->_getNotSecureImageUrl($imageUrl);

        //gallery
        $mediaGallery = $product->getMediaGalleryImages();
        foreach ($mediaGallery as $image) {
            $imageCollection[] = $this->_getNotSecureImageUrl($image->getUrl());
        }

        if (empty($imageCollection)) {
            return null;
        }

        $result = array();

        foreach ($imageCollection as $url){
            $data = array(
                'url'       => $url,
                'height'    => null,
                'width'     => null,
                'label'     => null,
                'altText'   => null
            );
            $result[] = $data;
        }

        return $result;
    }

    /**
     * @param $imageUrl
     * @return mixed
     */
    protected function _getNotSecureImageUrl($imageUrl)
    {
        if (strpos($imageUrl, 'https://') !== false) {
            $imageUrl = str_replace('https://', 'http://', $imageUrl);
        }
        return $imageUrl;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    protected function _getMappedAttributes(Mage_Catalog_Model_Product $product)
    {
        $attributeSetId = $product->getAttributeSetId();
        $attributeModel = Mage::getModel('xcom_mapping/attribute');
        $mappedAttributes   = $attributeModel->getSelectAttributesMapping($attributeSetId);

        return $mappedAttributes;
    }

    protected function _createProductTypeAttributeValue($key, $value, $type)
    {
        $result = array();
        switch ($type) {
            case 'string':
                $result['value'] = $this->_createProductTypeStringAttributeValue($key, $value);
                break;
            case 'enumeration': break;
            case 'boolean': break;
        }

        return $result;
    }

    protected function _createCustomAttributeValue($key, $value, $type)
    {
        $result = array();
        switch ($type) {
            case 'measurement':
            case 'string':
                $result['value'] = $this->_createStringAttributeValue($key, $value);
                break;
            case 'boolean':
                $result['value'] = $this->_createBooleanAttributeValue($key, $value);
                break;
        }

        return $result;
    }

    protected function _createProductTypeStringAttributeValue($name, $value)
    {
        $data = array(
            'valueId'        => $name,
            'attributeValue' => $this->_createStringAttributeValue($name, $value)
        );
        return $data;
    }

    protected function _createStringAttributeValue($name, $value)
    {
        $data = array(
            'attributeNameValue' => array($this->_createLocalizedNameValue($name, $value))
        );
        return $data;
    }

    protected function _createBooleanAttributeValue($name, $value)
    {
        $data = array(
            'value' => (bool)$value,
            'attributeName'    => array($this->_createLocalizedValue($name))
        );
        return $data;
    }

    /**
     * @param $name
     * @param $value
     * @return array|null
     */
    protected function _createLocalizedNameValue($name, $value)
    {
        $data = array(
            'locale'    => array(
                'language'  => $this->_locale[0],
                'country'   => $this->_locale[1],
                'variant'   => null
            ),
            'name'          => $name,
            'value'         => $value
        );

        return $data;
    }

    /**
     * @param $value
     * @return array|null
     */
    protected function _createLocalizedValue($value)
    {
        if (empty($value)) {
            return null;
        }

        $data = array(
            'locale'    => array(
                'language'  => $this->_locale[0],
                'country'   => $this->_locale[1],
                'variant'   => null
            ),
            'stringValue'   => $value
        );

        return $data;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _prepareProductOptions(Mage_Catalog_Model_Product $product)
    {
        $productData = array();
        return $productData;
    }
}
