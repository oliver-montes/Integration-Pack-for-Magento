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
 * @package     Xcom_Mapping
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Listing_Model_Message_Listing_Cancel_Failed extends Xcom_Listing_Model_Message_Listing_Response
{
    /**
     * Init object
     */
    protected function _construct()
    {
        $this->_schemaRecordName = 'CancelListingFailed';
        $this->_topic = 'listing/cancelFailed';
        $this->_newChannelProductStatus = Xcom_Listing_Model_Channel_Product::STATUS_ACTIVE;
        $this->_newChannelHistoryStatus = Xcom_Listing_Model_Channel_Product::STATUS_FAILURE;
        parent::_construct();
    }

    /**
     * @return bool
     */
    protected function _checkResponse()
    {
        $data = $this->getBody();
        if (!is_array($data) || !isset($data['errors']) || empty($data['errors']))
        {
            Mage::throwException('Message is not valid');
        }
        return parent::_checkResponse();
    }

    /**
     * @param array $items
     * @return array
     */
    protected function _collectProductSkus()
    {
        $data = $this->getBody();
        $skuArray = array();
        foreach ($data['errors'] as $item) {
            $skuArray[] = $item['sku'];
        }
        return $skuArray;
    }

}
