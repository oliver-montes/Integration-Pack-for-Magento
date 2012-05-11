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
class Xcom_Listing_Model_Message_Marketplace_CategoryForProductType_Search_Request
    extends Xcom_Xfabric_Model_Message_Request
{
    protected function _construct()
    {
        parent::_construct();
        $this->_topic = 'marketplace/categoryForProductType/search';
        $this->_schemaRecordName = 'SearchCategoriesForProductType';
    }

    /**
     * Prepare data for request.
     * Data options are used:
     *  - string|int product_type_id
     *  - string siteCode
     *  - string|null environmentName
     *  - array channel_codes (channelCode1, channelCode2)
     *
     * @return Xcom_Mapping_Model_Message_ProductType
     */
    protected function _prepareData(Varien_Object $data = null)
    {
        $messageData = array(
            'xProductTypeId'    => (string)$data->getData('product_type_id'),
            'siteCode'          => (string)$data->getData('siteCode'),
            'environmentName'   => $data->getData('environmentName'),
        );
        $this->setMessageData($messageData);
        return parent::_prepareData($data);
    }
}
