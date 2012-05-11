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
class Xcom_Listing_Model_Observer
{
    /**
     * @param $observer
     * @return Xcom_Listing_Model_Observer
     */
    public function sendRecommendedCategorySearch($observer)
    {
        if ($observer->getRelationProductTypeId()) {
            /** @var $category Xcom_Listing_Model_Resource_Category */
            $category = Mage::getResourceModel('xcom_listing/category');
            $mappingProductTypeId = (int)$observer->getMappingProductTypeId();
            $categoryIds = $category->getRecommendedCategoryIds($mappingProductTypeId);
            if (empty($categoryIds)) {
                $productType    = Mage::getModel('xcom_mapping/product_type')->load($mappingProductTypeId);
                if ($productType->getProductTypeId()) {
                    $this->_sendCategoryForProductTypeMessage($productType->getProductTypeId());
                }
            }
        }
        return $this;
    }

    /**
     * @param Varien_Object $observer
     * @return Xcom_Listing_Model_Observer
     */
    public function sendListingSearchRequest($observer)
    {
        try {
            $listingCollection = Mage::getResourceModel('xcom_listing/listing_collection')->load();
            foreach ($listingCollection as $listing) {
                $policy = Mage::getModel('xcom_mmp/policy')
                    ->load($listing->getPolicyId());
                $productSkus = $this->_getProductSkus($listing, $policy);
                if (count($productSkus)) {
                    $this->_sendListingSearchRequest($productSkus, $policy);
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * @param Varien_Object $listing
     * @param Varien_Object $policy
     * @return array
     */
    protected function _getProductSkus(Varien_Object $listing, Varien_Object $policy)
    {
        $productSkus = array();
        $collection = Mage::getResourceModel('xcom_listing/channel_product_collection')
            ->addChannelFilter($policy->getChannelId())
            ->addFieldToFilter('listing_id', $listing->getId())
            ->addFieldToFilter('market_item_id', array('notnull' => true))
            ->addFieldToSelect('product_id')
            ->addCatalogProducts();
        foreach ($collection as $item) {
            if ($item->hasSku()) {
                $productSkus[] = $item->getSku();
            }
        }
        return $productSkus;
    }

    /**
     * @param Varien_Object $policy
     * @param array $productSkus
     * @return Xcom_Listing_Model_Observer
     */
    protected function _sendListingSearchRequest(array $productSkus, Varien_Object $policy)
    {
        $options = array(
            'product_skus' => $productSkus,
            'xprofile_id'  => $policy->getXprofileId()
        );
        Mage::helper('xcom_xfabric')->send('listing/search', $options);
        return $this;
    }


    /**
     * Send request on marketplace/categoryForProductType/search topic
     * when we save mapping for magento attributes except mapping to "none" type
     *
     * @param int $productTypeId
     * @return Xcom_Mapping_Model_Product_Type
     */
    protected function _sendCategoryForProductTypeMessage($productTypeId)
    {
        /** @var $environment Xcom_Mmp_Model_Resource_Environment */
        $environment = Mage::getResourceModel('xcom_mmp/environment');
        /** @var $helper Xcom_Xfabric_Helper_Data */
        $helper = Mage::helper('xcom_xfabric');
        foreach ($environment->getAllEnvironments() as $record) {
            $options = array(
                'product_type_id' => $productTypeId,
                'siteCode'        => $record['site_code'],
                'environmentName' => $record['environment']
            );
            $helper->send('marketplace/categoryForProductType/search', $options);
        }
        return $this;
    }
}
