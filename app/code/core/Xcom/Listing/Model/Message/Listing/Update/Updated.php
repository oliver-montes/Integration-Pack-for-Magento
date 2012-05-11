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

class Xcom_Listing_Model_Message_Listing_Update_Updated extends Xcom_Listing_Model_Message_Listing_Response
{
    /**
     * Init message object
     */
    protected function _construct()
    {
        $this->_schemaRecordName = 'ListingUpdated';
        $this->_topic = 'listing/updated';
        $this->_newChannelProductStatus = Xcom_Listing_Model_Channel_Product::STATUS_ACTIVE;
        $this->_newChannelHistoryStatus = Xcom_Listing_Model_Channel_Product::STATUS_ACTIVE;
        parent::_construct();
    }

    /**
     * @return bool
     */
    protected function _checkResponse()
    {
        $data = $this->getBody();
        if (empty($data['updates']) || !is_array($data['updates'])) {
            throw Mage::exception('Mage_Core', Mage::helper('xcom_listing')->__('Message is not valid'));
        }
        return parent::_checkResponse();
    }

    /**
     * @return array
     */
    protected function _collectProductSkus()
    {
        $data = $this->getBody();
        $skuArray = array();
        foreach ($data['updates'] as $item) {
            if (isset($item['product']['sku'])) {
                $skuArray[] = $item['product']['sku'];
            }
        }
        return $skuArray;
    }
}
