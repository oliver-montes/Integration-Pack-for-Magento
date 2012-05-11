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
class Xcom_CseOffer_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param Xcom_Google_Model_Channel $channel
     * @param array $productIds
     * @return Xcom_Google_Adminhtml_Google_ProductController
     */
    public function processOffers($channel, array $productIds, $offerData)
    {
        /** @var $offer Xcom_Cse_Model_Offer */
        $offer = Mage::getModel('xcom_cseoffer/cseOffer');
        $offer->addData($offerData);
        $offer->prepareProducts($channel->getData('store_id'), $productIds);
        /** @var $validator Xcom_Cse_Helper_Offer_Validator */
        $validator = Mage::helper('xcom_cseoffer/validator');
        $validator->setCseOffer($offer);
        $validator->validateProducts();
        $options = array(
            'channel' => $channel
        );
        $offer->send($options);
        $offer->saveProducts();
        return $this;
    }
}
