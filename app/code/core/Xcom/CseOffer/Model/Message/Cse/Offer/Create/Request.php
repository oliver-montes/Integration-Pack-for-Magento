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

class Xcom_CseOffer_Model_Message_Cse_Offer_Create_Request extends Xcom_CseOffer_Model_Message_Cse_Offer_Request
{
	const FEED_TYPE_FULL = 'Full';
	const OFFER_STATE_NEW = 'NEW';
	
	/**
     * Schema version of the message (via X-XC-SCHEMA-VERSION)
     * @var string
     */
    protected $_schemaVersion = '3.0.0';

    /**
     * @var Xcom_Cse_Model_Account
     */
    protected $_account;
    
    /**
     * Prepare message object
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_schemaRecordName = 'CreateOffer';
        $this->_topic = 'cse/offer/create';
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
		$channel = $dataObject->getData('channel');
		$channelTypeCode = $channel->getData('channeltype_code');
        
        $this->setMessageData(array(
            'offers'		=> $this->_prepareAllOffers($dataObject->getData('products'), $channelTypeCode),
        	'feedType'		=> self::FEED_TYPE_FULL,
        	'xAccountId'	=> (string)$channel->getAccount()->getAuthId(),
        	'siteId'		=> $channel->getData('site_code'),
			'offerName'		=> $channel->getData('offer_name')
        ));
        return parent::_prepareData($dataObject);
    }

    /**
     * @param Xcom_Cse_Model_Account $account
     * @return Xcom_CseOffer_Model_Message_Cse_Offer_Create_Request
     */
    protected function _setAccount(Xcom_Cse_Model_Account $account)
    {
        $this->_account = $account;
        return $this;
    }
    
    /**
     * @return Xcom_Cse_Model_Account
     */
    public function getAccount()
    {
        return $this->_account;
    }
        
    /**
     * @param Varien_Object|null $options
     * @return Xcom_CseOffer_Model_Message_Cse_Offer_Create_Request
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

    /**
     * @throws Mage_Core_Exception
     * @param string $message
     * @return void
     */
    protected function _throwException($message)
    {
        throw Mage::exception('Mage_Core', Mage::helper('xcom_cseoffer')->__($message));
    }

    /**
     * Prepare CseOffer object for each product
     * Example of $products argument:
     * array(
     *     <product_id> => Mage_Catalog_Model_Product,
     *     <product_id> => Mage_Catalog_Model_Product,
     * )
     *
     * @param array $products
     * @return array
     */
    protected function _prepareAllOffers(array $products, $channelTypeCode)
    {
        $allOffers = array();
        foreach ($products as $product) {
            if (is_object($product) && $product->getId()) {
                $allOffers[] = $this->_prepareOfferInformation($product, $channelTypeCode);
            }
        }
        return $allOffers;
    }

    /**
     * @param Varien_Object $product
     * @return mixed
     */
    protected function _prepareEmbeddedObject(Varien_Object $product)
    {
        $payload = $this->_getCseOfferMessage()
            ->process(new Varien_Object(array(
                'product'   => $product
            )))
            ->getMessageData();

        $body = array(
			'fullName'		=> 'com.x.cse.v0.CseOffer',
            'schemaVersion' => '3.0.0',
            'schemaUri'     => null,
            'payload'       => $payload
        );
        return $body;
    }
    
    /**
     * @param Varien_Object $product
     * @return array
     */
    protected function _prepareOfferInformation(Varien_Object $product, $channelTypeCode)
    {
    	$store = Mage::app()->getStore($product->getData('store_id')); 
        return array(
            'id'				=> $product->getData('entity_id'),
            'state'				=> self::OFFER_STATE_NEW,	// Only support New state for now.
        	'channelId'			=> $channelTypeCode,
        	'channelAssignedId'	=> null,
        	'offerChannelUrl'	=> null,
        	'channelStatus'		=> null,
        	'sku'				=> $product->getData('sku'),
        	'price'				=> $this->_createCurrencyAmount($store, $product->getData('price')),
        	'quantity'			=> intval($product->getData('stock_item')->getData('qty')),
        	'startTime'			=> null,
        	'endTime'			=> null,
            'extension'  		=> $this->_prepareEmbeddedObject($product)
        );
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
    
    public function encode()
    {
        $this->_initSchema();
        $data = $this->getMessageData();
        if (!empty($data['offers'])) {
            foreach ($data['offers'] as &$offer) {
               if (isset($offer['extension']['payload'])) {
                    $offer['extension']['payload'] = $this->getEncoder()->encodeText(
                        $offer['extension']['payload'],
                        $this->_getCseOfferMessage()
                    );
                }
            }
        }
        $this->setBody($data);
        $this->getEncoder()->encode($this);
        return $this;
    }
    
    /**
     * @return Xcom_CseOffer_Model_Message_Cse_Offer_Extension_Request
     */
    protected function _getCseOfferMessage()
    {
        return Mage::helper('xcom_xfabric')->getMessage('cse/offer/extension');
    }
    
    /**
     * @param Mage_Catalog_Model_Product
     * @return array
     */
    public function getMappingOptions(Mage_Catalog_Model_Product $product)
    {
        return Mage::getSingleton('xcom_mapping/mapper')->getMappingOptions($product);
    }
}
