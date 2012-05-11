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

class Xcom_Listing_Model_Message_Listing_Specifics_Request extends Xcom_Xfabric_Model_Message_Request
{
    protected function _construct()
    {
        parent::_construct();
        $this->_schemaRecordName = 'ListingSpecifics';
        $this->_topic      = 'com.x.marketplace.ebay.v1/ListingSpecifics';
    }

    /**
     * Prepare data before sending
     *
     * @param Varien_Object $dataObject
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    public function _prepareData(Varien_Object $dataObject = null)
    {
        $this->setMessageData(array(
            'countryCode'   => $dataObject->getPolicy()->getData('location'),
            'listingFormat' => 'FixedPriceItem',
            'duration'      => '-1',
            'buyItNowPrice' => array(
                'amount' => $dataObject->getProduct()->getListingPrice() ?
                    (double)$dataObject->getProduct()->getListingPrice() : (double)0,
                'code'   => $dataObject->getPolicy()->getCurrency()
            ),
            'reservePrice' => array(
                'amount' => (double)0,
                'code' => 'USD'
            ),
            'payPalEmailAddress' => $dataObject->getPolicy()->getData('payment_paypal_email') ?
                (string)$dataObject->getPolicy()->getData('payment_paypal_email') : null,
            'immediatePaymentRequired' => $dataObject->getPolicy()->getData('payment_paypal_immediate')
                ? (bool)$dataObject->getPolicy()->getData('payment_paypal_immediate') : null,
            'handlingTime'  => strtolower($dataObject->getPolicy()->getHandlingTime()) != 'none' ?
                (int)$dataObject->getPolicy()->getHandlingTime() : null,
            'useTaxTable'   => $dataObject->getPolicy()->getData('apply_tax')
                ? (bool)$dataObject->getPolicy()->getData('apply_tax') : null,
            'postalCode'    => $dataObject->getPolicy()->getData('postal_code') ?
                $dataObject->getPolicy()->getData('postal_code') : '',
            'location'      => $dataObject->getPolicy()->getData('location') ?
                $dataObject->getPolicy()->getData('location') : null,
            'listingVariations' => null
        ));
        return parent::_prepareData($dataObject);
    }
}
