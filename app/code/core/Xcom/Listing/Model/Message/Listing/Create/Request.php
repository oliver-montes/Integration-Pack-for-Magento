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

class Xcom_Listing_Model_Message_Listing_Create_Request extends Xcom_Listing_Model_Message_Listing_Request
{
    /**
     * Max allowed images count
     */
    const MAX_IMAGES_COUNT = 12;

    /** @var array payment types accepted by integration */
    protected $_paymentTypes = array("AMEX", "CASH_ON_DELIVERY", "CHECK", "CREDIT_CARD",
                                     "DINERS", "DISCOVER", "ESCROW", "INTEGRATED_MERCHANT_CREDIT_CARD",
                                     "MASTERCARD", "MONEY_ORDER", "MONEY_TRANSFER", "MONEY_TRANSFER_IN_CHECKOUT",
                                     "MONEYBOOKERS", "PAYMATE", "PAYMENT_ON_PICKUP", "PAYMENT_SEE_DESCRIPTION",
                                     "PAYPAL", "PROPAY", "VISA");

    protected $_prepareAttributeMethods = array(
        'product_type_id'   => '_getProductTypeId',
        'image'             => '_getImageUrls'
    );

    /**
     * @var Xcom_Mmp_Model_Policy
     */
    protected $_policy;

    /**
     * Prepare message object
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_schemaRecordName = 'CreateListing';
        $this->_topic = 'listing/create';
        $this->_action = 'create';
    }

    /**
     * Prepare data before sending
     * @param Varien_Object $dataObject
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    public function _prepareData(Varien_Object $dataObject = null)
    {
        $this->_validateOptions($dataObject);
        $this->_setPolicy($dataObject->getPolicy());

        $this->setMessageData(array(
            'listings'   => $this->_prepareAllListings($dataObject->getData('products')),
            'xProfileId' => (string)$this->getPolicy()->getXprofileId()
        ));

        return parent::_prepareData($dataObject);
    }

    /**
     * @param Varien_Object|null $options
     * @throws Mage_Listing_Exception
     * @return Xcom_Listing_Model_Message_Listing_Create_Request
     */
    protected function _validateOptions($options)
    {
        if (!is_object($options)) {
            $this->_throwException('Options should be specified.');
        }

        if (!is_object($options->getPolicy())) {
            $this->_throwException('Policy should be specified.');
        }

        if (!is_object($options->getChannel())) {
            $this->_throwException('Channel should be specified.');
        }

        if (!is_array($options->getProducts())) {
            $this->_throwException('Products should be specified.');
        }
        return $this;
    }

    /**
     * @throws Mage_Core_Exception
     * @param string $message
     * @return void
     */
    protected function _throwException($message)
    {
        throw Mage::exception('Mage_Core', Mage::helper('xcom_listing')->__($message));
    }

    /**
     * @param Xcom_Mmp_Model_Policy $policy
     * @return Xcom_Listing_Model_Message_Listing_Create_Request
     */
    protected function _setPolicy(Xcom_Mmp_Model_Policy $policy)
    {
        $this->_policy = $policy;
        return $this;
    }

    /**
     * @return Xcom_Mmp_Model_Policy
     */
    public function getPolicy()
    {
        return $this->_policy;
    }

    /**
     * Prepare Listing object for each product
     * Example of $products argument:
     * array(
     *     <product_id> => Mage_Catalog_Model_Product,
     *     <product_id> => Mage_Catalog_Model_Product,
     * )
     *
     * @param array $products
     * @return array
     */
    protected function _prepareAllListings(array $products)
    {
        $allListings = array();
        foreach ($products as $product) {
            if (is_object($product) && $product->getId()) {
                $allListings[] = $this->_prepareListingInformation($product);
            }
        }
        return $allListings;
    }

    /**
     * @param Varien_Object $product
     * @return array
     */
    protected function _prepareListingInformation(Varien_Object $product)
    {
        return array(
            'xId'               => null,
            'marketItemId'      => null,
            'product'           => $this->_prepareProductOptions($product),
            'startTime'         => time(),
            'title'             => $product->getName(),
            'subTitle'          => null,
            'listingURL'        => null,
            'status'            => null,
            'giftWrapAvailable' => false,
            'marketCategories'  => $product->getListingCategoryId() ? array($product->getListingCategoryId()) : null,
            'customCategories'  => null,
            'price'             => $product->getListingPrice() ? $this->_getCurrencyAmount($product) : null,
            'quantity'          => $product->getListingQty(),
            'payment'           => $this->_preparePayment(),
            'shipping'          => null,
            'returnPolicy'      => $this->_prepareReturnPolicy(),
            'embeddedMessage'   => $this->_prepareEmbeddedMessage($product)
        );
    }

    /**
     * Prepare product attribute collection to send data
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _prepareProductOptions($product)
    {
        $productData = array();
        foreach($this->getListingAttributes() as $key => $attributeCode) {
            if (isset($this->_prepareAttributeMethods[$attributeCode])
                && method_exists($this, $this->_prepareAttributeMethods[$attributeCode])) {
                $method = $this->_prepareAttributeMethods[$attributeCode];
                $productData[$key] = $this->$method($product);
            } else {
                $productData[$key] = $product->getData($attributeCode);
            }
        }

        $productData['condition'] = null;
        $mappingData = $this->getMappingOptions($product);
        $productData['attributes'] = $this->prepareListingAttributeOptions($mappingData);

        return $productData;
    }

    /**
     * Returns listing attributes
     *
     * Returns listing attributes which should be added to the message.
     * Product values for these attributes will be used.
     *
     * @return array
     */
    public function getListingAttributes()
    {
        return array(
            'xId'               => 'xId',
            'xProductTypeId'    => 'product_type_id',//A unique identifier a product type
            'sku'               => 'sku',
            'manufacturer'      => 'manufacturer',
            'mpn'               => 'mpn',
            'brand'             => 'brand',
            'msrp'              => 'msrp',
            'minimumAdvertisedPrice' => 'minimumAdvertisedPrice',
            'imageURL'          => 'image',
            'description'       => 'description',
        );
    }

    /**
     * @param Mage_Catalog_Model_Product
     * @return array
     */
    public function getMappingOptions(Mage_Catalog_Model_Product $product)
    {
        return Mage::getSingleton('xcom_mapping/mapper')->getMappingOptions($product);
    }

    /**
     * Prepare options format for message
     *
     * @param array $mappingAttributeOptions
     * @return array|null
     */
    public function prepareListingAttributeOptions(array $mappingAttributeOptions)
    {
        $attributeOptions = array();
        foreach ($mappingAttributeOptions as $targetAttributeName => $targetAttributeValues) {
            if (!is_array($targetAttributeValues)) {
                $targetAttributeValues = array($targetAttributeValues);
            }
            $attributeOptions[] = array(
                'name' => $targetAttributeName,
                'value' => array_values($targetAttributeValues)
            );
        }
        return ((count($attributeOptions) > 0) ? $attributeOptions : null);
    }

    /**
     * Prepare CurrencyAmount object
     *
     * @param $product
     * @return array
     */
    protected function _getCurrencyAmount($product)
    {
        return array(
            'amount'    => (double)$product->getListingPrice(),
            'code'      => $this->getPolicy()->getCurrency(),
        );
    }

    /**
     * Prepare payment information based on policy
     *
     * @return null
     */
    protected function _preparePayment()
    {
        return null;
    }

     /**
     * @return array
     */
    protected function _prepareReturnPolicy()
    {
        if (!$this->getPolicy()->getReturnAccepted()) {
            return $this->_getNoReturnAcceptedRecord();
        }
        return $this->_getReturnAcceptedRecord();
    }

    /**
    * @return array
    */
    protected function _getNoReturnAcceptedRecord()
    {
        return array(
            'description'               => null,
            'returnAccepted'            => false,
            'buyerPaysReturnShipping'   => null,
            'returnByDays'              => null,
            'refundMethod'              => null
        );
    }

    /**
     * @return array
     */
    protected function _getReturnAcceptedRecord()
    {
        return array(
            'description'             => $this->getPolicy()->getReturnDescription(),
            'returnAccepted'          => true,
            'buyerPaysReturnShipping' => !is_null($this->getPolicy()->getShippingPaidBy())
                        ? ($this->getPolicy()->getShippingPaidBy() == 'buyer' ? true : false)
                        : null,
            'returnByDays'    => !is_null($this->getPolicy()->getReturnByDays()) ?
                (int) $this->getPolicy()->getReturnByDays() : null,
            'refundMethod'    => !is_null($this->getPolicy()->getRefundMethod()) ?
                $this->getPolicy()->getRefundMethod() : null
        );
    }

    /**
     * @param Varien_Object $product
     * @return mixed
     */
    protected function _prepareEmbeddedMessage(Varien_Object $product)
    {
        $payload = $this->_getListingSpecificsMessage()
            ->process(new Varien_Object(array(
                'policy'    => $this->getPolicy(),
                'product'   => $product
            )))
            ->getMessageData();

        $body = array(
            'schemaVersion' => '1.0.0',
            'schemaUri'     => null,
            'payload'       => $payload
        );
        return $body;
    }

    public function encode()
    {
        $this->_initSchema();
        $data = $this->getMessageData();
        if (!empty($data['listings'])) {
            foreach ($data['listings'] as &$listing) {
                if (isset($listing['embeddedMessage']['payload'])) {
                    $listing['embeddedMessage']['payload'] = $this->getEncoder()->encodeText(
                        $listing['embeddedMessage']['payload'],
                        $this->_getListingSpecificsMessage()
                    );
                }
            }
        }
        $this->setBody($data);
        $this->getEncoder()->encode($this);
        return $this;
    }

    /**
     * @return Xcom_Mmp_Model_Message_Marketplace_Profile_Specifics_Request
     */
    protected function _getListingSpecificsMessage()
    {
        return Mage::helper('xcom_xfabric')->getMessage('listing/create/listingSpecifics');
    }

    /**
     * Retrieve mapping_product_type_id by product attribute_set_id
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
     * Prepare standard product id based on product attributes
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
            $imageCollection[]  = $this->_getImageUrlOption($imageUrl);
        }
        //gallery
        $mediaGallery       = $product->getMediaGalleryImages();
        foreach ($mediaGallery as $image) {
            $imageCollection[]  = $this->_getImageUrlOption($image->getUrl());
        }
        //ebay support up to 12 images
        if (count($imageCollection) > self::MAX_IMAGES_COUNT) {
            $imageCollection    = array_slice($imageCollection, 0, self::MAX_IMAGES_COUNT);
        }
        return $imageCollection ? $imageCollection : null;
    }

    /**
     * Get data in ProductImage format
     *
     * @param $imageUrl
     * @return array
     */
    protected function _getImageUrlOption($imageUrl)
    {
        return array(
            'purpose'     => null,
            'locationURL' => $this->_getNotSecureImageUrl($imageUrl)
        );
    }

    /**
     * @param string $imageUrl
     * @return string
     */
    protected function _getNotSecureImageUrl($imageUrl)
    {
        if (strpos($imageUrl, 'https://') !== false) {
            $imageUrl = str_replace('https://', 'http://', $imageUrl);
        }
        return $imageUrl;
    }
}
