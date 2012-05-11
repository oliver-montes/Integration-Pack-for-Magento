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
class Xcom_Ebay_Model_CatalogInventory_Stock extends Mage_CatalogInventory_Model_Stock
{
    /**
     * Subtract product qtys from stock. Update product listing
     * Return array of items that require full save.
     *
     * @param array $items
     * @return array
     */
    public function registerProductsSale($items)
    {
        $savedItems  = parent::registerProductsSale($items);
        if ($savedItems && (Mage::helper('xcom_ebay')->isUpdateChannelToZeroOnInventoryToZero()
            || Mage::helper('xcom_ebay')->isUpdateChannelToZeroOnProductOutOfStock())
        ) {
            // Update listing for previously remembered items
            $productIds = array();
            foreach ($savedItems as $item) {
                $product    = Mage::getModel('catalog/product')->load($item->getProductId());
                $stockItem  = $product->getStockItem();
                if ((Mage::helper('xcom_ebay')->isUpdateChannelToZeroOnInventoryToZero() && $stockItem->getQty() == 0)
                    || (Mage::helper('xcom_ebay')->isUpdateChannelToZeroOnProductOutOfStock()
                        && $stockItem->getIsInStock() == 0)
                ) {
                    $productIds[] = $item->getProductId();
                }
            }
            if ($productIds) {
                Mage::helper('xcom_ebay')->updateListingForProduct($productIds);
            }
        }
        return $savedItems;
    }
}
