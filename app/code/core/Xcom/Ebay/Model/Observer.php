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
class Xcom_Ebay_Model_Observer
{
    /**
     * Update product listing if inventory qty is equal zero after changing inventory through admin
     *
     * @param  Varien_Event_Observer $observer
     * @return Xcom_Mmp_Model_Observer
     */
    public function synchronizeInventoryAfterStockChanges($observer)
    {
        /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem  = $observer->getEvent()->getItem();
        if ((Mage::helper('xcom_ebay')->isUpdateChannelToZeroOnInventoryToZero()
                && $stockItem->dataHasChangedFor('qty') && $stockItem->getQty() == 0)
            || (Mage::helper('xcom_ebay')->isUpdateChannelToZeroOnProductOutOfStock()
                && $stockItem->dataHasChangedFor('is_in_stock') && $stockItem->getIsInStock() == 0)
        ) {
            Mage::helper('xcom_ebay')->updateListingForProduct(array($stockItem->getProductId()));
        }
        return $this;
    }
}