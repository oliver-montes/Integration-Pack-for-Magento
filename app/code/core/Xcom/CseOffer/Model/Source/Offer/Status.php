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
class Xcom_CseOffer_Model_Source_Offer_Status
{
    /**
     * @return Xcom_CseOffer_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('xcom_cseoffer');
    }

    /**
     * @return array
     */
    public function toOptionHash()
    {
        return array(
            Xcom_CseOffer_Model_Channel_Product::STATUS_ACTIVE   => $this->getHelper()->__('Active'),
            Xcom_CseOffer_Model_Channel_Product::STATUS_INACTIVE  => $this->getHelper()->__('Inactive'),
            Xcom_CseOffer_Model_Channel_Product::STATUS_PENDING   => $this->getHelper()->__('Pending'),
            Xcom_CseOffer_Model_Channel_Product::STATUS_FAILURE   => $this->getHelper()->__('Failure')
        );
    }
}
