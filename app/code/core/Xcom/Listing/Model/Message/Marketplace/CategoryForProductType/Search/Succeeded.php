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
class Xcom_Listing_Model_Message_Marketplace_CategoryForProductType_Search_Succeeded
    extends Xcom_Xfabric_Model_Message_Response
{
    protected function _construct()
    {
        $this->_topic               = 'marketplace/categoryForProductType/searchSucceeded';
        $this->_schemaRecordName    = 'SearchProductTypeSucceeded';
        parent::_construct();
    }

    /**
     * @throws Mage_Core_Exception
     * @return Xcom_Listing_Model_Message_Marketplace_CategoryForProductType_Search_Succeeded
     */
    public function process()
    {
        parent::process();

        $data = $this->getBody();

        if (empty($data['xProductTypeId'])) {
            throw Mage::exception('Mage_Core', Mage::helper('xcom_listing')->__('Message is not valid. xProductTypeId is not defined.'));
        }

        $productType = Mage::getModel('xcom_mapping/product_type')->load($data['xProductTypeId'], 'product_type_id');
        $categories = array();
        foreach ($data['categories'] as $category) {
            $categories[] = $category['id'];
        }

        if($productType->getId() && count($categories)) {
            Mage::getResourceModel('xcom_listing/category')->importRelations(
                $productType->getId(), $categories, $data['marketplace'], $data['siteCode'], $data['environmentName']);
        }

        return $this;
    }
}
