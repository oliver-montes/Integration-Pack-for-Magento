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
class Xcom_CseOffer_Model_CseOffer extends Mage_Core_Model_Abstract
{
    const PRICE_TYPE_MAGE = 'magentoprice';
    /**
     * Array of products which are ready for publishing.
     * Example of array:
     *  array (
     *    <product_id> => Mage_Catalog_Model_Product object
     *    <product_id> => Mage_Catalog_Model_Product object
     *  )
     *
     * @var array
     */
    protected $_products = array();

    /**
     * Array of products from previous submission.
     * @var array
     */
    protected $_previousProducts;
    
    /**
     * Example of array:
     *  array (
     *    <cseoffer_id> => <product_id>
     *  )
     *
     * @var array
     */
    protected $_offerIds;

    protected function _construct()
    {
        $this->_init('xcom_cseoffer/cseoffer');
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isMagentoPriceType($type)
    {
        return self::PRICE_TYPE_MAGE == $type;
    }

    /**
     * @param array $productIds
     * @return Xcom_CseOffer_Model_Channel_Product
     */
    protected function _initProducts($storeId, array $productIds)
    {
        $this->_products = array();
        foreach ($productIds as $productId) {
            $this->_products[$productId] = Mage::getModel('catalog/product')
            	->setStoreId($storeId)
            	->load((int)$productId);
        }
        return $this;
    }

    /**
     * @param array $productIds
     * @return Xcom_CseOffer_Model_CseOffer
     */
    public function prepareProducts($storeId, array $productIds)
    {
    	// Initialize products.
        $this->_initProducts($storeId, $productIds);
        return $this;
    }

    /**
     * @return array
     */
    protected function _getPreviousSubmissionProductIds()
    {
        if (null === $this->_previousProducts) {
            $this->_previousProducts = Mage::getModel('xcom_cseoffer/channel_product')
                ->getPreviousSubmission($this->getChannelId());
        }
        return $this->_previousProducts;
    }
    
    /**
     * @param int $productId
     * @return int|null
     */
    protected function _getProductOfferId($productId)
    {
        $offerIds = $this->_getProductOfferIds();
        if (!empty($offerIds[$productId])) {
            return $offerIds[$productId];
        }
        return null;
    }

    /**
     * @return array
     */
    protected function _getProductOfferIds()
    {
        if (null === $this->_offerIds) {
            $this->_offerIds = Mage::getModel('xcom_cseoffer/channel_product')
                ->getProductOfferIds($this->getChannelId(), array_keys($this->getProducts()));
        }
        return $this->_offerIds;
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->_products;
    }

    /**
     *
     * @param array $options
     * @return Xcom_CseOffer_Model_Channel_Product
     */
    public function send(array $options)
    {
    	$productIds = array_keys($this->getProducts());
        $options['products'] = $this->_filterProducts($productIds);
        
        Mage::helper('xcom_xfabric')->send('cse/offer/create', $options);
        
        return $this;
    }

    /**
     * @param array $productIds
     * @return array
     */
    protected function _filterProducts(array $productIds)
    {
        $result = array();
        $products = $this->getProducts();
        foreach ($productIds as $productId) {
            if (isset($products[$productId])) {
                $result[$productId] = $products[$productId];
            }
        }
        return $result;
    }

    /**
     * @return Xcom_CseOffer_Model_CseOffer
     */
    public function saveProducts()
    {
        // Check to see if any products were removed from the original collection.
        // If so, remove from the database.
        // Do this prior to overriding the values upon the save.
       	$previousProductIds = Mage::getModel('xcom_cseoffer/channel_product')
        	->getPreviousSubmission($this->getChannelId());
        	
        $productIdsRemoved = array_diff($previousProductIds, array_keys($this->getProducts())); 

        foreach ($productIdsRemoved as $productIdRemoved) {
            Mage::getResourceSingleton('xcom_cseoffer/channel_product')
                ->deleteRelations($this->getChannelId(), $productIdRemoved);
        }  
        
        foreach ($this->getProducts() as $product) {
            $data = array(
                'offer_status'    => Xcom_CseOffer_Model_Channel_Product::STATUS_PENDING,
                'created_at'      => now(),
                'offer_id'        => (int)$this->getOfferId(),
            );
            Mage::getResourceSingleton('xcom_cseoffer/channel_product')
                ->saveRelations($this->getChannelId(), $product->getId(), $data);
        }
        return $this;
    }
}
