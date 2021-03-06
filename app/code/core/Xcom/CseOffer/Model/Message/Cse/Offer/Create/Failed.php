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

class Xcom_CseOffer_Model_Message_Cse_Offer_Create_Failed extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Prepare message object
     */
    protected function _construct()
    {
        $this->_schemaRecordName = 'CreateOfferFailed';
        $this->_topic = 'cse/offer/createFailed';
        $this->_newChannelProductStatus = Xcom_CseOffer_Model_Channel_Product::STATUS_FAILURE;
        $this->_newChannelHistoryStatus = Xcom_CseOffer_Model_Channel_Product::STATUS_FAILURE;
        parent::_construct();
    }

    /**
     * @return bool
     */
    protected function _checkResponse()
    {
        $data = $this->getBody();
        if (empty($data['errors']) || !is_array($data['errors'])) {
            Mage::throwException('Message is not valid');
        }
        return parent::_checkResponse();
    }
}
