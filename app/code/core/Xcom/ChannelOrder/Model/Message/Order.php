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
 * @package     Xcom_ChannelOrder
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_ChannelOrder_Model_Message_Order extends Mage_Core_Model_Abstract
{
    const PAYMENT_VALID_STAUS = 'PAID';

    protected $_orderLogType    = Xcom_Log_Model_Source_Type::TYPE_AUTOMATIC;
    /**
     * Customer data
     *
     * @var array
     */
    protected $_customerData;

    /**
     * Statuses list of order to protected update channel order
     *
     * @var array
     */
    protected $_protectedUpdateOrderStatuses = array(
        Mage_Sales_Model_Order::STATE_COMPLETE,
        Mage_Sales_Model_Order::STATE_CLOSED,
        Mage_Sales_Model_Order::STATE_CANCELED,
    );

    /**
     * Number of processed order
     *
     * @var string
     */
    protected $_orderNumber;

    protected $_orderMessageData;


    public function setOrderMessageData($data)
    {
        $this->_orderNumber      = $data['orderNumber'];
        $this->_orderMessageData = $data;
        return $this;
    }


    public function createOrder($data)
    {
        if (null === $this->_orderMessageData) {
            throw Mage::exception('Xcom_ChannelOrder',
                Mage::helper('xcom_channelorder')->__('Order has an empty body'));
        }
        $channelOrder = $this->getChannelOrder();
        if ($channelOrder->getOrderId()) {
            throw Mage::exception('Xcom_ChannelOrder',
                Mage::helper('xcom_channelorder')->__('Order #%s already exists', $this->_orderNumber));
        }

        $this->prepareQuote($data);
        $this->prepareShippingInfo($data);
        $this->getQuote()
            ->collectTotals()
            ->save();
        /** TODO need to make refactor of order save !!!  */
        try{
            $oconn = Mage::getSingleton('core/resource')->getConnection('write');
            $oconn->beginTransaction();
            $order = $this->saveOrder($data);
            $this->setFlatOrderId($order->getEntityId());
            $this->saveChannelOrderSpecific($data);
            $this->saveChannelOrderItems($data);
            $this->saveChannelPaymentSpecific($data);

            // create invoice
            $this->createInvoice();
            $oconn->commit();
        } catch(Exception $e) {
            $oconn->rollback();
            throw $e;
        }

        return $this;
    }

    /**
     * Update order
     *
     * @param array $data
     * @return Xcom_ChannelOrder_Model_Message_Order
     * @throws Mage_Core_Exception|Xcom_ChannelOrder_Exception
     */
    public function updateOrder(array $data)
    {
        $channelOrder   = $this->getChannelOrder();
        if (!$channelOrder->getOrderId()) {
            throw Mage::exception('Xcom_ChannelOrder',
                Mage::helper('xcom_channelorder')->__('Order #%s does not exist.', $this->_orderNumber));
        }

        if ($this->isOrderCanUpdate($channelOrder)) {
            $this->setFlatOrderId($channelOrder->getOrderId());
            $channelOrder->setStatus($data['status']);
            $channelOrder->save();
            $this->updateChannelPaymentSpecific($data);
        }

        // create invoice
        $this->createInvoice();
        return $this;
    }

    /**
     * Create invoice if it's possible
     *
     * @return bool
     */
    public function createInvoice()
    {
        if (!$this->validateInvoice()) {
            return false;
        }
        try {
            $channelOrder = $this->getChannelOrder();
            /** @var $invoice Mage_Sales_Model_Order_Invoice */
            $magentoOrderModel = Mage::getModel("sales/order")->load((int)$channelOrder->getOrderId());
            $magentoOrderModel->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
            $invoice = Mage::getModel('sales/service_order', $magentoOrderModel)
                ->prepareInvoice();
            if (!$invoice->getTotalQty()) {
                Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
            }
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
            $invoice->register();
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());

            $transactionSave->save();
            return true;
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
        } catch(Exception $e) {
            Mage::logException($e);
        }
        return false;
    }

    /**
     * Check if invoice can be created
     *
     * @return bool
     */
    public function validateInvoice()
    {
        $channelOrder   = $this->getChannelOrder();
        if (!$channelOrder->getOrderId()) {
            return false;
        }

        if (!Mage::getModel('sales/order')->load((int)$channelOrder->getOrderId())->canInvoice()) {
            return false;
        }

        // check order status, valid status is only PAID
        if (self::PAYMENT_VALID_STAUS != strtoupper($channelOrder->getPayment()->getPaymentStatus())) {
            return false;
        }

        return true;
    }


    public function prepareShippingInfo($data)
    {
        $helper = Mage::helper('xcom_channelorder');
        $shippingId = $this->getOrderData('shipment_id');

        if (empty($shippingId) || !isset($data['shipments'])) {
            throw Mage::exception('Xcom_ChannelOrder',
                $helper->__('Order #%s contains no shipments', $this->_orderNumber));
        }
        $shipment = array_shift($data['shipments']);
        if (empty($shipment['shipmentId']) || $shipment['shipmentId'] != $shippingId){
            throw Mage::exception('Xcom_ChannelOrder',
                $helper->__('Shipment did not match', $this->_orderNumber));
        }
        $this->setShippingFees($shipment['shippingFees']['amount']);

        $trackingDetail = isset($shipment['trackingDetails']) ? array_shift($shipment['trackingDetails']) : null;
        if (!$trackingDetail) {
            return $this;
        }
        $this->setShippingCarrier($trackingDetail['carrier']);
        $this->setShippingService($trackingDetail['service']);
        return $this;
    }

    /**
     * Check available to update channel order. Method return exception when
     *
     * @param Xcom_ChannelOrder_Model_Order $channelOrder
     * @return bool
     * @throws Mage_Core_Exception
     * @throws Exception
     */
    public function isOrderCanUpdate(Xcom_ChannelOrder_Model_Order $channelOrder)
    {
        if (!$channelOrder->getId()) {
            throw new Exception('Channel order is not initialized.');
        }

        $order = $channelOrder->getOrder();
        if (!$order->getId()) {
            throw new Exception('Order not found.');
        }

        $status = $order->getData('status');

        if (in_array($status, $this->_protectedUpdateOrderStatuses)) {
            $status = ucfirst($status);
            throw Mage::exception('Xcom_ChannelOrder',
                Mage::helper('xcom_channelorder')->__('Order #%s has status "%s" cannot be updated.', $this->_orderNumber, $status));
        }
        return true;
    }

    /**
     * Get channel order model by external order number
     *
     * @return Xcom_ChannelOrder_Model_Order
     * @throws Xcom_ChannelOrder_Exception
     */
    public function getChannelOrder()
    {
        if (empty($this->_orderNumber)) {
            throw Mage::exception('Xcom_ChannelOrder',
                Mage::helper('xcom_channelorder')->__('Order contains no orderNumber'));
        }

        $channelOrder = Mage::getModel('xcom_channelorder/order')
            ->load($this->_orderNumber, 'order_number');

        return $channelOrder;
    }

    /**
     * Prepare quote item
     *
     * @param array $data
     * @return Xcom_ChannelOrder_Model_Message_Order
     */
    public function prepareQuote(array $data)
    {
        if (empty($data['grandTotal'])) {
            throw Mage::exception('Xcom_ChannelOrder',
                Mage::helper('xcom_channelorder')->__('Order #%s contains no grandTotal', $this->_orderNumber));
        }
        $this->addItemsToQuote($data)
            ->addCustomerToQuote($data)
            ->addBillingAddressToQuote($data)
            ->addShippingAddressToQuote($data);

        $this->getQuote()
            ->setGrandTotal($data['grandTotal']['amount'])
            ->setBaseCurrencyCode($data['grandTotal']['code'])
            ->reserveOrderId()
            ->setStoreId($this->_getStoreId());

        return $this;
    }

    /**
     * Add order items to quote
     *
     * @param array $data
     * @return Xcom_ChannelOrder_Model_Message_Order
     * @throws Xcom_ChannelOrder_Exception
     */
    public function addItemsToQuote(array $data)
    {
        $helper = Mage::helper('xcom_channelorder');

        if (empty($data['orderItems']) || !count($data['orderItems'])) {
            throw Mage::exception('Xcom_ChannelOrder',
                $helper->__('Order #%s contains no products', $this->_orderNumber));
        }

        $items = array();
        foreach ($data['orderItems'] as $item) {
            $productSku = trim($item['productSku']);
            if (empty($productSku)) {
                throw Mage::exception('Xcom_ChannelOrder',
                    $helper->__('Order #%s contains product with an empty value for SKU',
                        $this->_orderNumber));
            }
            if (empty($item['itemId'])) {
                throw Mage::exception('Xcom_ChannelOrder',
                    $helper->__('Order #%s contains product with empty itemId',
                        $this->_orderNumber));
            }
            /** @var $quoteItem Mage_Sales_Model_Quote_Item */
            $quoteItem = Mage::getModel('sales/quote_item');
            $quoteItem->setQty($item['quantity']);
            $quoteItem->setPrice($item['price']['price']['amount']);
            /** @var $product Mage_Catalog_Model_Product */
            $product = Mage::helper('xcom_mmp')->getProductBySku($item['productSku']);
            if (!$product->getEntityId()) {
                throw Mage::exception('Xcom_ChannelOrder',
                    $helper->__('Order #%s contains product with the wrong SKU \'%s\'',
                        $this->_orderNumber, $item['productSku']));
            }
            $product->setPrice($item['price']['price']['amount']);
            $product->setFinalPrice($item['price']['price']['amount']);
            $quoteItem->setProduct($product);
            $this->getQuote()->addItem($quoteItem);

            $info['item_id'] = $product->getEntityId();

            $listing = Mage::getModel('xcom_listing/channel_product')
                ->load($item['itemId'], 'market_item_id');
            $info['channel_id'] = $listing->getChannelId();
            if (empty($info['channel_id'])) {
                throw Mage::exception('Xcom_ChannelOrder',
                    $helper->__('Order #%s contains products the system could not find published. Wrong itemId.',
                        $this->_orderNumber));
            }

            $items[$item['productSku']] = $info;
            $orderData['shipment_id'] = $item['shipmentId'];
        }
        $orderData['items'] = $items;
        $this->setOrderData($orderData);
        return $this;
    }

    /**
     * Retrieve quote model object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('adminhtml/session_quote')->getQuote();
    }


    /**
     * Add customer to quote
     *
     * @param array $data
     * @return Xcom_ChannelOrder_Model_Message_Order
     * @throws Xcom_ChannelOrder_Exception
     */
    public function addCustomerToQuote(array $data)
    {
        if (empty($data['customer'])) {
            throw Mage::exception('Xcom_ChannelOrder',
                Mage::helper('xcom_channelorder')->__('Order #%s contains no customer data', $this->_orderNumber));
        }
        $customer = $data['customer'];
        $this->_customerData['email']      = $customer['email']['emailAddress'];
        $this->_customerData['firstname']  = $customer['name']['firstName'];
        $this->_customerData['middlename'] = $customer['name']['middleName'];
        $this->_customerData['lastname']   = $customer['name']['lastName'];
        $this->_customerData['prefix']     = $customer['name']['prefix'];
        $this->_customerData['suffix']     = $customer['name']['suffix'];

        $customer = Mage::getModel('customer/customer');
        $customer->addData($this->_customerData);
        $this->getQuote()->setCustomer($customer);
        return $this;
    }

    /**
     * Add billing address to quote
     *
     * @param array $data
     * @return Xcom_ChannelOrder_Model_Message_Order
     * @trows Xcom_ChannelOrder_Exception
     */
    public function addBillingAddressToQuote(array $data)
    {
        if (empty($data['customer'])) {
            throw Mage::exception('Xcom_ChannelOrder',
                Mage::helper('xcom_channelorder')->__('Order #%s contains no customer data', $this->_orderNumber));
        }
        $customer = $data['customer'];
        $billing['address_id'] = null;
        $billing['region_id']  = null;
        $billing['country_id'] = 'US';
        $billing['firstname']  = $customer['name']['firstName'];
        $billing['lastname']   = $customer['name']['middleName'];
        $billing['lastname']  .= ' ' . $customer['name']['lastName'];
        $billing['email']      = $customer['email']['emailAddress'];

        $billingAddress        = $data['billingAddress'];
        $billing['street'][0]  = $billingAddress['street1'] . ' ' . $billingAddress['street2'];
        $billing['street'][1]  = $billingAddress['street3'] . ' ' . $billingAddress['street4'];
        $billing['city']       = $billingAddress['city'];
        $billing['region']     = $billingAddress['stateOrProvince'];
        $billing['postcode']   = $billingAddress['postalCode'];
        $billing['telephone']  = isset($customer['phone']['number']) ? $customer['phone']['number'] : null;

        /** @var $billingAddress Mage_Sales_Model_Quote_Address */
        $billingAddress = Mage::getModel('sales/quote_address');
        $billingAddress->setData($billing);
        $billingAddress->implodeStreetAddress();

        $this->getQuote()->setBillingAddress($billingAddress);
        return $this;
    }

    /**
     * Add shipping address to quote
     *
     * @param array $data
     * @return Xcom_ChannelOrder_Model_Message_Order
     * @trows Xcom_ChannelOrder_Exception
     */
    public function addShippingAddressToQuote(array $data)
    {
        if (empty($data['destination'])) {
            throw Mage::exception('Xcom_ChannelOrder',
                Mage::helper('xcom_channelorder')->__('Order #%s contains no destination data', $this->_orderNumber));
        }

        $shippingAddress        = $data['destination'];
        $shipping['address_id'] = null;
        $shipping['region_id']  = null;
        $shipping['country_id'] = 'US';
        $shipping['firstname']  = $shippingAddress['name']['firstName'];
        $shipping['lastname']   = $shippingAddress['name']['middleName'];
        $shipping['lastname']  .= ' ' . $shippingAddress['name']['lastName'];
        $shipping['street'][0]  = $shippingAddress['address']['street1'];
        $shipping['street'][0] .= ' ' . $shippingAddress['address']['street2'];
        $shipping['street'][1]  = $shippingAddress['address']['street3'];
        $shipping['street'][1] .= ' ' . $shippingAddress['address']['street4'];
        $shipping['city']       = $shippingAddress['address']['city'];
        $shipping['region']     = $shippingAddress['address']['stateOrProvince'];
        $shipping['postcode']   = $shippingAddress['address']['postalCode'];
        $shipping['telephone']  = isset($data['customer']['phone']['number'])
            ? $data['customer']['phone']['number'] : null;

        $shippingAddress = Mage::getModel('sales/quote_address');
        $shippingAddress->setData($shipping);
        $shippingAddress->implodeStreetAddress();
        $this->getQuote()->setShippingAddress($shippingAddress);
        return $this;
    }

    /**
     * Save order via Mage_Sales_Model_Order
     *
     * @param array $data
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function saveOrder($data)
    {
        // use only for save order to use own resource model without using rewrite
        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('xcom_channelorder/order_save');

        /** @var $helper Mage_Core_Helper_Data */
        $helper = Mage::helper('core');
        $helper->copyFieldset('customer_account', 'to_quote',
            $this->_customerData, $order);

        $quote = $this->getQuote();

        $shippingDescription = $this->getShippingService() ? $this->getShippingService() : $helper->__('Flat Rate');
        $order->setIncrementId($quote->getReservedOrderId())
            ->setStoreId($this->_getStoreId())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
            ->setState(Mage_Sales_Model_Order::STATE_NEW)
            ->setStatus('pending')
            ->setShippingDescription($shippingDescription)
            ->setQuoteId($quote->getEntityId());

        if (isset($data['dateOrdered'])) {
            $date  = new Zend_Date($data['dateOrdered'],Varien_Date::DATETIME_INTERNAL_FORMAT);
            $order->setCreatedAt( $date->setTimezone('UTC')->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        }

        /** @var $converter Mage_Sales_Model_Convert_Quote */
        $converter  = Mage::getModel('sales/convert_quote');
        $order->setBillingAddress(
            $converter->addressToOrderAddress($quote->getBillingAddress())
        );
        $order->setShippingAddress(
            $converter->addressToOrderAddress($quote->getShippingAddress())
        );
        $order->setPayment(
            $converter->paymentToOrderPayment($quote->getPayment()->setMethod('free'))
        );

        foreach ($quote->getAllItems() as $item) {
            $orderItem = $converter->itemToOrderItem($item);
            $order->addItem($orderItem);
        }

        /** TODO Update grandtotal from message instead of using quote->grandtotal */
        $order->setGrandTotal($quote->getGrandTotal() + $this->getShippingFees());
        $order->setBaseGrandTotal($quote->getBaseGrandTotal() + $this->getShippingFees());
        $order->setBaseCurrencyCode($quote->getBaseCurrencyCode());
        $order->setGlobalCurrencyCode($quote->getBaseCurrencyCode());
        $order->setStoreCurrencyCode($quote->getBaseCurrencyCode());
        $order->setOrderCurrencyCode($quote->getBaseCurrencyCode());
        $order->setSubtotal($quote->getBaseGrandTotal());
        $order->setSubtotalInclTax($quote->getBaseGrandTotal());
        $order->setBaseSubtotal($quote->getBaseGrandTotal());
        $order->setBaseSubtotalInclTax($quote->getBaseGrandTotal());
        $order->setShippingAmount($this->getShippingFees());
        $order->setBaseShippingAmount($this->getShippingFees());


        $order->setQuote($quote);

        /*$transaction = Mage::getModel('core/resource_transaction');
        $transaction->addObject($order);
        $transaction->addCommitCallback(array($order, 'save'));*/

        Mage::dispatchEvent('sales_model_service_quote_submit_before',
            array('order' => $order, 'quote' => $quote));
        try {
            /*$transaction->save();*/
            $order->save();
            Mage::dispatchEvent('sales_model_service_quote_submit_success',
                array('order' => $order, 'quote' => $quote));
        } catch (Exception $e) {

            //reset order ID's on exception, because order not saved
            $order->setId(null);
            /** @var $item Mage_Sales_Model_Order_Item */
            foreach ($order->getItemsCollection() as $item) {
                $item->setOrderId(null);
                $item->setItemId(null);
            }

            Mage::dispatchEvent('sales_model_service_quote_submit_failure',
                array('order' => $order, 'quote' => $quote));
            throw $e;
        }
        Mage::dispatchEvent('sales_model_service_quote_submit_after',
            array('order' => $order, 'quote' => $quote));
        return $order;
    }

    /**
     * @param array $data
     * @return Xcom_ChannelOrder_Model_Message_Order
     */
    public function saveChannelOrderSpecific(array $data)
    {
        $channelId = $this->getOrderChannelId();

        Mage::getModel('xcom_channelorder/order')
            ->setChannelId($channelId)
            ->setOrderId($this->getFlatOrderId())
            ->setXaccountId($this->getAccountId())
            ->setOrderNumber($data['orderNumber'])
            ->setDateOrdered($data['dateOrdered'])
            ->setSource($data['source'])
            ->setSourceId($data['sourceId'])
            ->setStatus($data['status'])
            ->setShippingCarrier($this->getShippingCarrier())
            ->setShippingCost($this->getShippingFees())
            ->save();

        return $this;
    }

    /**
     * @param array $data
     * @return Xcom_ChannelOrder_Model_Message_Order
     * @throws Xcom_ChannelOrder_Exception
     */
    public function saveChannelOrderItems(array $data)
    {
        $orderData = $this->getOrderData();
        /** save order items specific */
        foreach ($data['orderItems'] as $item) {
            $productSku = trim($item['productSku']);
            if (empty($productSku) || empty($item['itemId'])) {
                $message = Mage::helper('xcom_channelorder')
                    ->__('Order #%s contains product with an empty value for SKU or ItemId', $this->_orderNumber);
                throw Mage::exception('Xcom_ChannelOrder', $message);
            }

            /** @var $channelOrderItem Xcom_ChannelOrder_Model_Order_Item */
            $channelOrderItem = Mage::getModel('xcom_channelorder/order_item');
            $channelOrderItem->setOrderId($this->getFlatOrderId())
                ->setOrderItemId($orderData['items'][$item['productSku']]['item_id'])
                ->setItemNumber($item['itemId'])
                ->setOfferUrl($item['offerUrl'])
                ->save();
        }
        return $this;
    }

    /**
     * Get order channel ID
     *
     * @return int
     * @throws Xcom_ChannelOrder_Exception
     */
    public function getOrderChannelId()
    {
        $channelId = 0;
        if (!$this->getOrderData()) {
            return $channelId;
        }

        $orderData = $this->getOrderData();
        foreach ($orderData['items'] as $sku => $product) {
            if ($channelId == 0) {
                $channelId = $product['channel_id'];
            }
            if ($channelId != $product['channel_id']) {
                throw Mage::exception('Xcom_ChannelOrder',
                    Mage::helper('xcom_channelorder')->__("Order contains product from another channel. SKU: %s", $sku));
            }
        }
        return $channelId;
    }

    /**
     * @param array $data
     * @return Xcom_ChannelOrder_Model_Message_Order
     */
    public function saveChannelPaymentSpecific(array $data)
    {
        if (is_array($data['paymentMethods'])) {
            foreach ($data['paymentMethods'] as $method) {
                $this->_validatePaymentMethodsData($method);
                Mage::getModel('xcom_channelorder/order_payment')
                    ->setOrderId($this->getFlatOrderId())
                    ->setExternalTransactionId($method['transactionId'])
                    ->setDatePaid($method['datePaid'])
                    ->setTransactionStatus($method['transactionStatus'])
                    ->setPaymentStatus($method['paymentStatus'])
                    ->setFee($method['processingFee']['amount'])
                    ->setMethod($method['method']['method'])
                    ->setAmount($data['grandTotal']['amount'])
                    ->setCurrency($data['grandTotal']['code'])
                    ->save();
            }
        }
        return $this;
    }

    /**
     * Update payment info
     *
     * @param array $data
     * @return Xcom_ChannelOrder_Model_Message_Order
     */
    public function updateChannelPaymentSpecific(array $data)
    {
        $payment = Mage::getModel('xcom_channelorder/order_payment')->load($this->getFlatOrderId(), 'order_id');
        if (!$payment->getId()) {
            $this->saveChannelPaymentSpecific($data);
        } else {
            foreach ($data['paymentMethods'] as $method) {
                $this->_validatePaymentMethodsData($method);
                $payment->setExternalTransactionId($method['transactionId'])
                    ->setDatePaid($method['datePaid'])
                    ->setTransactionStatus($method['transactionStatus'])
                    ->setPaymentStatus($method['paymentStatus'])
                    ->setFee($method['processingFee']['amount'])
                    ->setMethod($method['method']['method'])
                    ->save();
            }
        }
        return $this;
    }

    /**
     * Validate payment method received data from message
     *
     * @param array $method
     * @throws Mage_Core_Exception
     */
    protected function _validatePaymentMethodsData($method)
    {
        if (empty($method['method']['method'])) {
            $message = Mage::helper('xcom_channelorder')
                ->__('Order #%s contains an empty value for paymentMethod', $this->_orderNumber);
            throw Mage::exception('Xcom_ChannelOrder', $message);
        }
    }

    /**
     * Retrieve order number
     *
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->_orderNumber;
    }

    /**
     * Retrieve store view from channel by account id and site code
     *
     * @return int
     */
    protected function _getStoreId()
    {
        $storeId    = Mage::getResourceModel('xcom_mmp/channel')
            ->getStoreId($this->getAccountId(), $this->getSiteCode());
        return $storeId ? $storeId : Mage_Core_Model_App::ADMIN_STORE_ID;
    }

}
