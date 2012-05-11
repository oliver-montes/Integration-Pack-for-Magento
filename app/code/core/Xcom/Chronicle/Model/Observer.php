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
 * @package     Xcom_Chronicle
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Chronicle_Model_Observer
{
    /**
     * Triggered when new order is created
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Chronicle_Model_Observer
     */
    public function orderAfterCreate(Varien_Event_Observer $observer)
    {
        try {
            if ($order = $observer->getEvent()->getOrder()) {
                //single shipping order
                Mage::helper('xcom_xfabric')->send('order/created', array('order' => $order));
            } else if ($observer->getEvent()->getOrders()) {
                //multi shipping case
                foreach ($observer->getEvent()->getOrders() as $order) {
                    Mage::helper('xcom_xfabric')->send('order/created', array('order' => $order));
                }
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered after order cancellation
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Chronicle_Model_Observer
     */
    public function orderAfterCancel(Varien_Event_Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            Mage::helper('xcom_xfabric')->send('order/cancelled', array('order' => $order));
            $this->_registerValue('xcom_order_cancelled', $order->getId());
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered after order is shipped
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Chronicle_Model_Observer
     */
    public function orderAfterShip(Varien_Event_Observer $observer)
    {
        try {
            $shipment = $observer->getEvent()->getShipment();

            if ($this->_isShipmentNew($shipment)) {
                //only publish shipped on new record.  Do not publish shipped again if they added
                //tracking information separately - this will be reflected in order update
                Mage::helper('xcom_xfabric')->send('order/shipment/shipped', array('shipment' => $shipment));
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }
    /**

     * Triggered before order is saved
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Chronicle_Model_Observer
     */
    public function orderBeforeSave(Varien_Event_Observer $observer)
    {
        if (!$observer->getEvent()->getOrder()->getId() && !Mage::registry('xcom_order_new')) {
            Mage::register('xcom_order_new', true, true);
        }
        return $this;
    }

    /**
     * Triggered after order is saved
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Chronicle_Model_Observer
     */
    public function orderAfterSave(Varien_Event_Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();

            $isUpdated = $this->_isValueRegistered('xcom_order_updated', $order->getId());

            if (!Mage::registry('xcom_order_new') && !$isUpdated) {
                Mage::helper('xcom_xfabric')->send('order/updated', array('order' => $order));
                $this->_registerValue('xcom_order_updated', $order->getId());
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered after shippment is saved
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Chronicle_Model_Observer
     */
    public function orderUpdateAfterShip(Varien_Event_Observer $observer)
    {
        try {
            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();

            $isUpdated = $this->_isValueRegistered('xcom_order_updated', $order->getId());

            if (!$isUpdated) {
                //publish order updated because shipment has been changed and it is not new
                Mage::helper('xcom_xfabric')->send('order/updated', array('order' => $order));
                $this->_registerValue('xcom_order_updated', $order->getId());
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }

        return $this;
    }

    /**
     * Triggered after order address is saved
     *
     * @param Varien_Event_Observer $observer
     * @return Xcom_Chronicle_Model_Observer
     */
    public function orderAddressAfterSave(Varien_Event_Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getAddress()->getOrder();

            $isCancelled = $this->_isValueRegistered('xcom_order_cancelled', $order->getId());
            $isUpdated = $this->_isValueRegistered('xcom_order_updated', $order->getId());

            if (!Mage::registry('xcom_order_new') && !$isCancelled && !$isUpdated) {
                Mage::helper('xcom_xfabric')->send('order/updated', array('order' => $order));
                $this->_registerValue('xcom_order_updated', $order->getId());
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }

        return $this;
    }

    protected function _isShipmentNew($shipment) {
        $origIdValue = $shipment->getOrigData('id');
        if (isset($origIdValue)) {
            return false;
        }
        return true;
    }

    /**
     * Triggered on event customer_save_before
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerBeforeSave(Varien_Event_Observer $observer)
    {
        try {
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered on event customer_save_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerAfterSave(Varien_Event_Observer $observer)
    {
        try {
            $customer = $observer->getEvent()->getCustomer();

            if ($this->_isCustomerNew($customer)) {
                Mage::helper('xcom_xfabric')->send('customer/created', array('customer' => $customer));
            }
            else {
                Mage::helper('xcom_xfabric')->send('customer/updated', array('customer' => $customer));
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered on event customer_delete_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerAfterDelete(Varien_Event_Observer $observer)
    {
        try {
            $customer = $observer->getCustomer();
            Mage::helper('xcom_xfabric')->send('customer/deleted', array('customer' => $customer));
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    protected function _isCustomerNew($customer) {
        $origIdValue = $customer->getOrigData('id');
        if (isset($origIdValue)) {
            return false;
        }
        return true;
    }

    /**
     * Triggered on event catalog_product_save_before
     *
     * @param Varien_Event_Observer $observer
     */
    public function productBeforeSave(Varien_Event_Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            $isSimple = $this->_isSimpleProduct($product);

            if ($isSimple) {
                if (!$product->isObjectNew()) {
                    /* store products that have already existed */
                    $this->_registerValue('xcom_product_old', $product->getId());
                    $this->_registerKeyValue('xcom_product_change', $product->getId(), $this->_getProductMessage($product));
                }

            // If product is added to new stores we will get duplicate saves.  Store 0 is the only one that matters.
    //        if($product->getStoreId() != 0) {
    //            return $this;
    //        }

                /*
                    needs to register if the sku has changed in case future observer
                    event methods like inventoryAfterSave need it
                */
                $originalSku = $product->getOrigData('sku');
                $newSku = $product->getSku();
                if ($originalSku != $newSku) {
                    $this->_registerValue('xcom_product_changed_sku', $product->getId());
                }

                /* Need to store the old websites(stores) for this product */
                $c = $product->getIsChangedWebsites()?'true':'false';

                $dbProduct = Mage::getModel('catalog/product')->load($product->getId());
                $this->_registerKeyValue('xcom_offer_old_stores', $product->getId(), $dbProduct->getStoreIds());
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered on event catalog_product_save_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function productAfterSave(Varien_Event_Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            if ($this->_isSimpleProduct($product)) {
                if (!$this->_isValueRegistered('xcom_product_old', $product->getId())) {
                    Mage::helper('xcom_xfabric')->send('com.x.pim.v1/ProductCreation/ProductCreated', array('product' => $product));
                }
                else {
                    $prevProductAsArray = $this->_getRegisterValueForKey('xcom_product_change', $product->getId());
                    if (!empty($prevProductAsArray)) {
                        $curProductAsArray =  Mage::getModel('xcom_chronicle/message_product', $product)->toArray();
                        if ($this->_arrayRecursiveDiff($curProductAsArray, $prevProductAsArray)) {
                            Mage::helper('xcom_xfabric')->send('com.x.pim.v1/ProductUpdate/ProductUpdated', array('product' => $product));
                        }
                    }
                }

                // If product is added to new stores we will get duplicate saves.  Store 0 is the only one that matters.
        //        if($product->getStoreId() != 0) {
        //            return $this;
        //        }

                // Decide if new offer or update or cancelled
                $this->_sendOfferMessages($product);
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }


    /**
     * Triggered on event catalog_product_delete_before
     *
     * @param Varien_Event_Observer $observer
     */
    public function productBeforeDelete(Varien_Event_Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();

            $dbProduct = Mage::getModel('catalog/product')->load($product->getId());
            $this->_registerKeyValue('xcom_offer_old_stores', $product->getId(), $dbProduct->getStoreIds());
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered on event catalog_product_delete_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function productAfterDelete(Varien_Event_Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            if ($this->_isSimpleProduct($product)) {
                Mage::helper('xcom_xfabric')->send('com.x.pim.v1/ProductDeletion/ProductDeleted', array('product' => $product));

                $this->_sendOfferMessages($product, true);
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    protected function _isProductNew($product) {
        $origIdValue = $product->getOrigData('id');
        if (isset($origIdValue)) {
            return true;
        }
        return false;
    }

    /**
     * Triggered on event cataloginventory_stock_item_save_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function inventoryAfterSave(Varien_Event_Observer $observer)
    {
        try {
            $stockItem = $observer->getEvent()->getItem();

            $productId = $stockItem->getProductId();
            $product = Mage::getModel('catalog/product')->load((int)$productId);

            if (isset($product) && $this->_isSimpleProduct($product)) {
                $sku = $product->getSku();
                if (!$this->_isValueRegistered('xcom_product_old', $productId)) {
                    Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemUpdate/StockItemUpdated',
                        array('stock_item' => $stockItem,  'product_sku' => $sku));
                    $this->_registerValue('xcom_inventory_updated', $productId);
                }
                else {
                    /* only send data if it is changed */
                    $originalQty = $stockItem->getOrigData('qty');
                    $newQty = $stockItem->getQty();

                    /* set in the productAfterSave */
                    $isSkuChanged = $this->_isValueRegistered('xcom_product_changed_sku', $stockItem->getProductId());

                    if (($newQty != $originalQty) || ($isSkuChanged)) {
                        Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemUpdate/StockItemUpdated',
                            array('stock_item' => $stockItem,  'product_sku' => $sku));
                        $this->_registerValue('xcom_inventory_updated', $productId);
                    }
                }

                $inStockOrig = $stockItem->getOrigData('is_in_stock');
                $inStockNew = $stockItem->getIsInStock();
                if ((!$stockItem->verifyStock())
                    || ($inStockOrig != $inStockNew && !$inStockNew)) {
                    /* if it is Out of Stock and Stock Availability was a value that was changed */
                    Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemUpdate/OutOfStock',
                        array('stock_item' => $stockItem, 'product_sku' => $sku));
                }
//                $this->_sendOfferMessages($product);
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    protected function _isSimpleProduct(Mage_Catalog_Model_Product $product)
    {
        return $product->getTypeId() == 'simple';
    }

    protected function _getProductMessage(Mage_Catalog_Model_Product $product)
    {
        $products = Mage::getResourceModel('catalog/product_collection')
            ->addFieldToFilter('entity_id',$product->getEntityId());
        $products->load();
        $origProduct = Mage::getModel('catalog/product')->load((int)$product->getEntityId());
        return Mage::getModel('xcom_chronicle/message_product', $origProduct)->toArray();
    }

//    protected function _hasProductChanged(Mage_Catalog_Model_Product $product)
//    {
//        $products = Mage::getResourceModel('catalog/product_collection')
//            ->addFieldToFilter('entity_id',$product->getEntityId());
//        $products->load();
//        $origProduct = Mage::getModel('catalog/product')->load((int)$product->getEntityId());
//        $origAsArray = Mage::getModel('xcom_chronicle/message_product', $origProduct)->toArray();
//        $curAsArray =  Mage::getModel('xcom_chronicle/message_product', $product)->toArray();
//        $diff = $this->_arrayRecursiveDiff($curAsArray, $origAsArray);
//        return !empty($diff);
//    }

    protected function _arrayRecursiveDiff($srcArray, $destArray) {
        $aReturn = array();

        foreach ($srcArray as $mKey => $mValue) {
            if (!empty($destArray) && array_key_exists($mKey, $destArray)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = $this->_arrayRecursiveDiff($mValue, $destArray[$mKey]);
                    if (count($aRecursiveDiff)) {
                        $aReturn[$mKey] = $aRecursiveDiff;
                     }
                }
                else {
                    if ($mValue != $destArray[$mKey]) {
                        $aReturn[$mKey] = $mValue;
                    }
                 }
            }
            else {
                $aReturn[$mKey] = $mValue;
            }
        }
        return $aReturn;
    }

    /**
     * Triggered on event checkout_submit_all_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function publishInventoryForQuoteEvent(Varien_Event_Observer $observer)
    {
        try {
            /* after a checkout */
            $quote = $observer->getEvent()->getQuote();
            $items = $quote->getAllItems();

            foreach ($items as $item) {
                $product = Mage::getModel('catalog/product')->load((int)$item->getProductId());
                if (isset($product) && $this->_isSimpleProduct($product)) {
                    $sku = $product->getSku();
                    if (!$this->_isValueRegistered('xcom_inventory_updated', $item->getProductId())) {
                        Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemUpdate/StockItemUpdated',
                            array('stock_item' => $product->getStockItem(),  'product_sku' => $sku));
                        $this->_registerValue('xcom_inventory_updated', $item->getProductId());
                    }
                    $stockItem = $item->getProduct()->getStockItem();

                    $inStockOrig = $stockItem->getOrigData('is_in_stock');
                    $inStockNew = $stockItem->getIsInStock();
                    if ((!$stockItem->verifyStock())
                        || ($inStockOrig != $inStockNew && !$inStockNew)) {
                        /* if it is Out of Stock and Stock Availability was a value that was changed */
                        Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemUpdate/OutOfStock',
                            array('stock_item' => $stockItem, 'product_sku' => $sku));
                    }

                    $this->_sendOfferMessages($product);
                }
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    /**
     * Triggered on event sales_order_item_cancel
     *
     * @param Varien_Event_Observer $observer
     */
    public function inventoryAfterOrderItemCancel(Varien_Event_Observer $observer)
    {
        try {
            $item = $observer->getEvent()->getItem();
            $product = Mage::getModel('catalog/product')->load((int)$item->getProductId());

            if (isset($product) && $this->_isSimpleProduct($product)) {
                if (!$this->_isValueRegistered('xcom_inventory_updated', $item->getProductId())) {
                    Mage::helper('xcom_xfabric')->send('com.x.inventory.v1/StockItemUpdate/StockItemUpdated',
                        array('stock_item' => $product->getStockItem(),  'product_sku' => $product->getSku()));
                    $this->_registerValue('xcom_inventory_updated', $item->getProductId());
                }

                $this->_sendOfferMessages($product);
            }
        } catch (Exception $exception) {
            $this->_handleException($exception);
        }
        return $this;
    }

    protected function _sendOfferMessages(Mage_Catalog_Model_Product $product, $performThoroughCheck = false)
    {

        if($this->_isValueRegistered('xcom_offer_messages_sent', $product->getId())) {
            return;
        }
        $this->_registerValue('xcom_offer_messages_sent', $product->getId());

        $cancelledSids = array();
        $createdSids = array();

        if($performThoroughCheck || $product->getIsChangedWebsites()) {
            $oldWids = $this->_getRegisterValueForKey('xcom_offer_old_stores', $product->getId());
            if (!isset($oldWids)) {
                $oldWids = array();
            }
            $cancelledSids = array_diff($oldWids, $product->getStoreIds());
            $createdSids = array_diff($product->getStoreIds(), $oldWids);
        }

        $updatedSids = array_diff($product->getStoreIds(), $cancelledSids, $createdSids);

        // There is a case where product was duplicated and just now a sku was filled in
        $oldSku = $product->getOrigData('sku');
        $sku = $product->getSku();
        if(empty($oldSku) && !empty($sku)) {
            $createdSids = array_merge($createdSids, $updatedSids);
            $updatedSids = array();
        }

        if($product->dataHasChangedFor('price')
            || $product->dataHasChangedFor('visibility')
            || $product->dataHasChangedFor('status')
            || $this->_isValueRegistered('xcom_inventory_updated', $product->getId())) {
            $updatedOffers = array();
            foreach($updatedSids as $sid) {
                $offer = Mage::getModel('xcom_chronicle/message_saleschannel_offer',
                    array('product'  => $product,
                        'store_id'   => $sid,));
                $updatedOffers[] = $offer->toArray();
            }
            if(!empty($updatedOffers)) {
                Mage::helper('xcom_xfabric')->send('salesChannel/offer/updated',
                    array('offers' => $updatedOffers));
            }
        }

        $createdOffers = array();
        foreach($createdSids as $sid) {
            $offer = Mage::getModel('xcom_chronicle/message_saleschannel_offer',
                array('product'  => $product,
                    'store_id' => $sid));

            $createdOffers[] = $offer->toArray();
        }
        if(!empty($createdOffers)) {
            Mage::helper('xcom_xfabric')->send('salesChannel/offer/created',
                array('offers' => $createdOffers));
        }

        $cancelledOffers = array();
        foreach($cancelledSids as $sid) {
            $offer = Mage::getModel('xcom_chronicle/message_saleschannel_offer',
                array('product'  => $product,
                    'store_id' => $sid));

            $cancelledOffers[] = $offer->toArray();
        }
        if(!empty($cancelledOffers)) {
            $cancelledIds = array();
            foreach ($cancelledOffers as $offer) {
                $cancelledIds[] = $offer['id'];
            }
            Mage::helper('xcom_xfabric')->send('salesChannel/offer/cancelled',
                array('offer_ids' => $cancelledIds));
        }
    }

    /**
     * Sets a value in the Mage::registry with a key based on registerName and key.
     *
     * @param $registerName Mage::register name to use
     * @param $key The key in the Mage::register array used
     * @param $value the value to set
     */
    protected function _registerKeyValue($registerName, $key, $value)
    {
        $array = Mage::registry($registerName);

        if (!isset($array)) {
            $array = array();
        } else {
            /* yes, unregister it so that we can push it back on */
            Mage::unregister($registerName);
        }

        $array[$key] = $value;

        Mage::register($registerName, $array);
    }

    /**
     * Gets the value registered in the Mage::registry and the key (e.g. order id).
     *
     * @param $registerName Mage::register name to use
     * @param $key The key in the Mage::register array used
     * @return the value registered, null if no value
     */
    protected function _getRegisterValueForKey($registerName, $key)
    {
        $array = Mage::registry($registerName);

        if (isset($array)) {
            if (isset($array[$key])) {
                return $array[$key];
            }
        }

        return null;
    }

    /**
     * Registers a value (such as an order or product id) in an array acting
     * as a set.
     *
     * @param $registerName Mage::register name to use
     * @param $value The value (e.g. id) to put in the set
     */
    protected function _registerValue($registerName, $value)
    {
        $set = Mage::registry($registerName);

        if (!isset($set)) {
            $set = array();
        } else {
            /* yes, unregister it so that we can push it back on */
            Mage::unregister($registerName);
        }

        if (!in_array($value, $set)) {
            array_push($set, $value);
        }

        Mage::register($registerName, $set);
    }

    /**
     * Determines if a value has been registered
     *
     * @param $registerName Mage::register name to use
     * @param $value The value (e.g. id) to put in the set
     * @return true if the value is in the set, false otherwise
     */
    protected function _isValueRegistered($registerName, $value)
    {
        $set = Mage::registry($registerName);
        if (isset($set) && in_array($value, $set)) {
            return true;
        }

        return false;
    }

    protected function _handleException(Exception $exception)
    {
        Mage::logException($exception);
    }
}
