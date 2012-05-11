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
 * @package     Xcom_Ebay
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class of source model of eBay accounts
 *
 * @category   Xcom
 * @package    Xcom_Ebay
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Xcom_Ebay_Model_Source_Account extends Xcom_Mmp_Model_Source_AccountAbstract
{
    /**
     * Get channel type code for the eBay channel
     *
     * @return array
     */
    public function getChannelTypeCode()
    {
        /** @var $helper Xcom_Ebay_Helper_Data */
        $helper = Mage::helper('xcom_ebay');
        return $helper->getChanneltypeCode();
    }

    /**
     * Returns array of accounts with status = 1.
     *
     * @return array
     */
    public function getActiveAccountHash()
    {
        $this->setActiveFilter();
        return $this->toOptionHash();
    }
}
