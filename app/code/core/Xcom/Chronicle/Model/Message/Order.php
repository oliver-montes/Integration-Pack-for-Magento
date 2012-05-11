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

class Xcom_Chronicle_Model_Message_Order extends Varien_Object
{
    // Status enum
    const STATUS_NEW = 'NEW';
    const STATUS_ON_HOLD = 'ON_HOLD';
    const STATUS_BACKORDERED = 'BACKORDERED';
    const STATUS_PENDING_PAYMENT = 'PENDING_PAYMENT';
    const STATUS_READY_TO_SHIP = 'READYTOSHIP';
    const STATUS_PARTIALLY_SHIPPED = 'PARTIALLY_SHIPPED';
    const STATUS_SHIPPED = 'SHIPPED';
    const STATUS_PROCESSING_RETURN = 'PROCESSING_RETURN';
    const STATUS_EXCHANGED = 'EXCHANGED';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_COMPLETED = 'COMPLETED';

    // Payment methods enum
    const PAYMENT_METHOD_AMEX = 'AMEX';
    const PAYMENT_METHOD_CASH_ON_DELIVERY = 'CASH_ON_DELIVERY';
    const PAYMENT_METHOD_CHECK = 'CHECK';
    const PAYMENT_METHOD_CREDIT_CARD = 'CREDIT_CARD';
    const PAYMENT_METHOD_DINERS = 'DINERS';
    const PAYMENT_METHOD_DISCOVER = 'DISCOVER';
    const PAYMENT_METHOD_ESCROW = 'ESCROW';
    const PAYMENT_METHOD_INTEGRATED_MERCHANT_CREDIT_CARD = 'INTEGRATED_MERCHANT_CREDIT_CARD';
    const PAYMENT_METHOD_MASTERCARD = 'MASTERCARD';
    const PAYMENT_METHOD_MONEY_ORDER = 'MONEY_ORDER';
    const PAYMENT_METHOD_MONEY_TRANSFER = 'MONEY_TRANSFER';
    const PAYMENT_METHOD_MONEYBOOKERS = 'MONEYBOOKERS';
    const PAYMENT_METHOD_PAYMATE = 'PAYMATE';
    const PAYMENT_METHOD_PAYMENT_ON_PICKUP = 'PAYMENT_ON_PICKUP';
    const PAYMENT_METHOD_PAYPAL = 'PAYPAL';
    const PAYMENT_METHOD_PROPAY = 'PROPAY';
    const PAYMENT_METHOD_VISA = 'VISA';

    private $_paymentMethods = array(
        'ccsave'                    => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_CREDIT_CARD,
        'checkmo'                   => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_CHECK,
        'free'                      => 'FREE',
        'purchaseorder'             => 'PURCHASE_ORDER',
        'googlecheckout'            => 'GOOGLE_CHECKOUT',
        'authorizenet'              => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_CREDIT_CARD,
        'paypal_express'            => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_PAYPAL,
        'paypal_direct'             => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_PAYPAL,
        'paypal_standard'           => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_PAYPAL,
        'paypaluk_express'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_PAYPAL,
        'paypaluk_direct'           => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_PAYPAL,
        'verisign'                  => 'VERISIGN',
        'paypal_billing_agreement'  => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_PAYPAL,
        'payflow_link'              => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_PAYPAL,
        'hosted_pro'                => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_PAYPAL,
        'authorizenet_directpost'   => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_CREDIT_CARD,
        'paypal_mep'                => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_PAYPAL,
        'moneybookers_acc'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_csi'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_did'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_dnk'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_ebt'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_ent'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_gcb'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_gir'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_idl'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_lsr'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_mae'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_npy'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_pli'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_psp'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_pwy'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_sft'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_so2'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_wlt'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
        'moneybookers_obt'          => Xcom_Chronicle_Model_Message_Order::PAYMENT_METHOD_MONEYBOOKERS,
    );

    private $_baseCurrencyCode = null;
    private $_channelOrder = null;
    private $_siteCode = null;
    private $_accountId = null;
    private $_orderNumber = null;

    public function __construct(Mage_Sales_Model_Order $order)
    {
        if(null === $order) {
            return;
        }
        $this->_setupIfChannelOrder($order);
        $this->setData($this->_createOrder($order));
    }

    /**
     * Returns true if the Mage_Sales_Model_Order used to construct this
     * Order is a channel order
     * @return bool
     */
    public function isChannelOrder()
    {
        if(null === $this->_channelOrder) {
            return false;
        }
        $data = $this->_channelOrder->getData();
        return !empty($data);
    }

    /**
     * Returns a cached account id that will change depending on
     * whether this is a channel order or not.
     * @return null
     */
    public function getAccountId()
    {
        return $this->_accountId;
    }

    /**
     * Returns a cached site code that will changed depending on
     * whether this is a channel order or not.
     * @return null
     */
    public function getSiteCode()
    {
        return $this->_siteCode;
    }

    /**
     * Helper to get resource model from xcom order.
     * Can be used in unit testing to inject data
     * @return Object
     */
    protected function _getXcomOrderResource()
    {
        return Mage::getResourceModel('xcom_channelorder/order');
    }

    /**
     * Queries the xcom order table to see if this order is a channel order.
     * If a corresponding channel order data is found then it stores the
     * channel order model in _channelOrder.
     * @param $order_id
     * @return null
     */
    protected function _getXcomOrderModel($order_id)
    {
        $orders = $this->_getXcomOrderResource();
        if(empty($orders)) {
            return;
        }
        $this->_channelOrder = Mage::getModel('xcom_channelorder/order');
        $orders->load($this->_channelOrder, $order_id, 'order_id');
        return $this->_channelOrder;
    }

    /**
     * Takes an order in
     * @param Mage_Sales_Model_Order $order
     * @return mixed
     */
    protected function _setupIfChannelOrder(Mage_Sales_Model_Order $order)
    {
        if(null === $this->_channelOrder) {
            $this->_channelOrder = $this->_getXcomOrderModel($order->getId());

            if(null === $this->_channelOrder) {
                return;
            }
            $data = $this->_channelOrder->getData();
            $emptyData = empty($data);

            if(!$emptyData) {
                $channel = Mage::getModel('xcom_mmp/channel')->load($this->_channelOrder->getChannelId());
                $this->_accountId = $channel->getXaccountId();
                $this->_siteCode = $channel->getSiteCode();

            }
        }
        // cache the proper order number
        $this->_orderNumber = $this->_getOrderNumber($order);
    }

    /**
     * Returns a cached order number for this order.  The order number
     * can change if this is a channel order.  Allows for re-use by
     * Shipment object.
     * @return null
     */
    public function getOrderNumber()
    {
        return $this->_orderNumber;
    }

    /**
     * Finds and caches the order number for this order
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    protected function _getOrderNumber(Mage_Sales_Model_Order $order)
    {
        if($this->isChannelOrder()) {
            return $this->_channelOrder->getOrderNumber(); //getChannelOrderId();
        }
        return $order->getRealOrderId();
    }

    /**
     * Finds and caches the source for the order
     * @return string
     */
    protected function _getSource()
    {
        if($this->isChannelOrder()) {
            return $this->_channelOrder->getSource();
        }
        return 'Magento';
    }

    /**
     * Finds and caches the sourceId for the order
     * @return string
     */
    protected function _getSourceId()
    {
        if($this->isChannelOrder()) {
            return $this->_channelOrder->getSourceId();
        }
        return Mage::getBaseUrl();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function _createOrder(Mage_Sales_Model_Order $order)
    {
        $this->_baseCurrencyCode = $order->getBaseCurrency()->getCurrencyCode();
        $shippingAddress = $order->getShippingAddress();

        $data = array(
            'orderNumber'           => $this->_getOrderNumber($order),
            'purchaseOrder'         => null,
            'dateOrdered'           => date('c', strtotime($order->getCreatedAt())),
            'source'                => $this->_getSource(),
            'sourceId'              => $this->_getSourceId(),
            'status'                => $this->_createOrderStatus($order),
            'customer'              => $this->_createCustomer($order),
            'billingAddress'        => null,  //$this->_createAddress($order->getBillingAddress()),
            'grandTotal'            => $this->_createCurrencyAmount($order->getBaseGrandTotal()),
            'itemPriceTotal'        => $this->_createCurrencyAmount($order->getBaseSubtotal()),
            'totalInsuranceCost'    => null,
            'totalTaxAmount'        => $this->_createCurrencyAmount($order->getBaseTaxAmount()),
            'totalDiscountAmount'   =>  $this->_createCurrencyAmount($order->getBaseDiscountAmount()),
            'totalShippingFees'     => $this->_createCurrencyAmount($order->getBaseShippingAmount()),
            'paymentMethods'        => $this->_createPayment($order->getPayment()),
            'orderItems'            => $this->_createOrderItems($order->getItemsCollection(),
                $order->getShipmentsCollection()),//TODO: only parents or not?
            'destination'           => $this->_createShipTo(empty($shippingAddress) ? null : $shippingAddress),
            'shipments'             => $this->_createShipments($order->getShipmentsCollection()),
            'extension'             => null,
        );
        return $data;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function _createCustomer(Mage_Sales_Model_Order $order)
    {
        $data = array(
            'channelAssignedCustomerId' => $this->_makeNullSafe($order->getCustomerId()),
            'email'                     => array(
                'emailAddress'  => $order->getCustomerEmail(),
                'extension'     => null
            ),
            'name'                      => $this->_createCustomerName($order),
            'phone'                     => null, // Information not available
        );
        return $data;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    protected function _createOrderStatus(Mage_Sales_Model_Order $order)
    {
        $status = $order->getStatus();
        $state = $order->getState();
        if ($state == Mage_Sales_Model_Order::STATE_NEW && $status == 'pending' ) {
            return Xcom_Chronicle_Model_Message_Order::STATUS_NEW;
        }
        if ($state == Mage_Sales_Model_Order::STATE_HOLDED  && $status == 'holded') {
            return Xcom_Chronicle_Model_Message_Order::STATUS_ON_HOLD;
        }
        if ($state == Mage_Sales_Model_Order::STATE_CANCELED && $status == 'canceled') {
            return Xcom_Chronicle_Model_Message_Order::STATUS_CANCELLED;
        }
        if ($state == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            return Xcom_Chronicle_Model_Message_Order::STATUS_PENDING_PAYMENT;
        }
        if ($state == Mage_Sales_Model_order::STATE_COMPLETE) {
            return Xcom_Chronicle_Model_Message_Order::STATUS_COMPLETED;
        }
        if ($state == Mage_Sales_Model_Order::STATE_PROCESSING && $status == 'processing') {
            //if no items shipped == ready to ship
            //if some items shipped == partially shipped
            //if all items shipped == shipped

            if ($order->getIsVirtual()) {
                //If this is a virtual order then it must not be invoiced yet
                return Xcom_Chronicle_Model_Message_Order::STATUS_PENDING_PAYMENT;
            }

            if (count($order->getShipmentsCollection())==0 && $order->getIsNotVirtual()) {
                //No shipments have been made & there are nonVirtual items
                return Xcom_Chronicle_Model_Message_Order::STATUS_READY_TO_SHIP;
            }
            else {
                //Some or potentially all shipments have been made
                foreach ($order->getAllItems() as $item) {
                    if ($item->getQtyToShip()>0 && !$item->getIsVirtual()) {
                        //non virtual items yet to be shipped
                        return Xcom_Chronicle_Model_Message_Order::STATUS_PARTIALLY_SHIPPED;
                    }
                }
            }
            //if we made it here we must have shipped every non virtual item
            return Xcom_Chronicle_Model_Message_Order::STATUS_SHIPPED;
        }
        if ($state == Mage_Sales_Model_Order::STATE_CLOSED ){
            if ($order->getBaseTotalRefunded() == $order->getBaseGrandTotal() ) {
                return Xcom_Chronicle_Model_message_order::STATUS_CANCELLED;
            }
        }

        return Xcom_Chronicle_Model_Message_Order::STATUS_NEW;
    }

    protected function _createShipTo(Mage_Sales_Model_Order_Address $address)
    {
        $data = array(
            'name'      => $this->_createAddressName($address),
            'address'   => $this->_createAddress($address),
            'giftTag'   => null,
        );
        return $data;
    }

    protected function _createCustomerName(Mage_Sales_Model_Order $order)
    {
        $data = array(
            'firstName'     => $order->getCustomerFirstname(),
            'middleName'    => strlen($order->getCustomerMiddlename()) > 0 ? $order->getCustomerMiddlename() : null,
            'lastName'      => $order->getCustomerLastname(),
            'prefix'        => strlen($order->getCustomerPrefix()) > 0 ? $order->getCustomerPrefix() : null,
            'suffix'        => strlen($order->getCustomerSuffix()) > 0 ? $order->getCustomerSuffix() : null
        );

        return $data;
    }

    /**
     * // TODO Stolen from Customer.php - need this in a helper
     * @param mixed $address // Mage_Sales_Model_Order_Address or Mage_Sales_Model_Order
     * @return array
     */
    protected function _createAddressName(Mage_Sales_Model_Order_Address $address)
    {
        $data = array(
            'firstName'     => $address->getFirstname(),
            'middleName'    => strlen($address->getMiddlename()) > 0 ? $address->getMiddlename() : null,
            'lastName'      => $address->getLastname(),
            'prefix'        => strlen($address->getPrefix()) > 0 ? $address->getPrefix() : null,
            'suffix'        => strlen($address->getSuffix()) > 0 ? $address->getSuffix() : null
        );

        return $data;
    }

    /**
     * @param Mage_Sales_Model_Order_Address $address
     * @return array|null
     */
    protected function _createAddress(Mage_Sales_Model_Order_Address $address)
    {
        if (empty($address)) {
            return null;
        }
        $region = $address->getRegion();
        $data = array(
            'street1'           => $address->getStreet1(),
            'street2'           => $address->getStreet2(),
            'street3'           => $address->getStreet3(),
            'street4'           => $address->getStreet4(),
            'city'              => $address->getCity(),
            'county'            => null,
            'stateOrProvince'   => empty($region) ? null : $region,
            'postalCode'        => $address->getPostcode(),
            'country'           => Mage::getModel('directory/country')
                ->loadByCode($address->getCountryId())
                ->getIso3Code()
        );

        return $data;
    }

    /**
     * @param $amount
     * @return array
     */
    protected function _createCurrencyAmount($amount)
    {
        return array(
            'amount'    => (string)$amount,
            'code'      => $this->_baseCurrencyCode,
        );
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return array
     */
    protected function _createPayment(Mage_Sales_Model_Order_Payment $payment)
    {
        return array( array(
            'method'            => array(
                'method' => $this->_convertPaymentMethod($payment->getMethod())
            ),
            'datePaid'          => date('c', strtotime($payment->getCreatedAt())),
            'transactionId'     => $payment->getTxnId(),
            'transactionStatus' => $payment->getTxnType(),
            'paymentStatus'     => null, // TODO
            'processingFee'     => null, // TODO
        ));
    }

    /**
     * TODO
     * @param $paymentMethod
     * @return string
     */
    protected function _convertPaymentMethod($paymentMethod)
    {
        $mappedMethod = $this->_paymentMethods[$paymentMethod];
        if(empty($mappedMethod)) {
            $mappedMethod = $paymentMethod;
        }
        return $mappedMethod;
    }

    /**
     * @param Mage_Sales_Model_Mysql4_Order_Item_Collection $orderItemCollection
     * @param Mage_Sales_Model_Mysql4_Order_Shipment_Collection $shipmentCollection
     * @return array
     */
    protected function _createOrderItems(Mage_Sales_Model_Mysql4_Order_Item_Collection $orderItemCollection,
                                         Mage_Sales_Model_Mysql4_Order_Shipment_Collection $shipmentCollection)
    {
        $map = array();
        foreach ($shipmentCollection as $shipment) {
            foreach ($shipment->getItemsCollection() as $item) {
                $int_qty = ((int)$item->getQty());
                $map[$item->getOrderItem()->getItemId()][] = array(
                    'qty' => ((int)$item->getQty()),
                    'shipmentId' => $shipment->getIncrementId(),
                );
            }
        }

        $data = array();
        foreach ($orderItemCollection as $item) {
            if (isset($map[$item->getItemId()])) {
                $shippedCount = 0;
                foreach ($map[$item->getItemId()] as $shipment) {
                    $data[] = $this->_createOrderItem($item, $shipment['qty'], $shipment['shipmentId']);
                    $shippedCount = $shippedCount + $shipment['qty'];
                }
                if ($shippedCount < $item->getQtyOrdered()) {
                    $data[] = $this->_createOrderItem($item, $item->getQtyOrdered - $shippedCount);
                }
            } else {
                $data[] = $this->_createOrderItem($item);
            }
        }
        return $data;
    }

    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @param null $qty
     * @param null $shipmentId
     * @return array
     */
    protected function _createOrderItem(Mage_Sales_Model_Order_Item $item, $qty = null, $shipmentId = null)
    {
        $data = array(
            'itemId' => $item->getItemId(),
            'productSku' => $item->getSku(),
            'quantity' => isset($qty) ? (int)$qty : (int)$item->getQtyOrdered(),
            'status' => 'active',
            'offerId' => null,
            'offerUrl' => null, //Mage::getBaseUrl(),
            'price' => $this->_createOrderItemPrice($item),
            'destination' => null,
            'shipmentId' => $shipmentId,
            'extension' => null
        );
        return $data;
    }

    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @return array
     */
    protected function _createOrderItemPrice(Mage_Sales_Model_Order_Item $item)
    {
        $discount = $item->getBaseDiscountAmount();
        $tax = $item->getBaseTaxAmount();

        $data = array(
            'price'          => $this->_createCurrencyAmount($item->getBaseRowTotal()),
            'discountAmount' => empty($discount) ? null : $this->_createCurrencyAmount($item->getBaseDiscountAmount()),
            'insuranceCost'  => null,
            'taxAmount'      => empty($tax) ? null : $this->_createCurrencyAmount($item->getBaseTaxAmount()),
            'discounts'      => null,
            'additionalCost' => null,
        );
        return $data;
    }

    /**
     * @param Mage_Sales_Model_Mysql4_Order_Shipment_Collection $shipmentCollection
     * @return array
     */
    protected function _createShipments(Mage_Sales_Model_Mysql4_Order_Shipment_Collection $shipmentCollection)
    {
        $data = array();
        foreach ($shipmentCollection as $shipment) {
            $data[] = $this->_createShipment($shipment);
        }
        $d = var_export($data, true);
        return $data;
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     */
    protected function _createShipment(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $data = array(
            'shipmentId' => $shipment->getIncrementId(),
            //'shipToAddress' => $this->_createAddress(),
            //'cost' => $this->_createCurrencyAmount(),
            'shippingFees' => $this->_createCurrencyAmount('0.00'),
            'discountAmount' => null,
            'discounts' => null,
            'additionalCost' => null,
            'packagingHandlingCost' => null,
            'surcharge' => null,
            'trackingDetails' => $this->_createTrackingDetails($shipment),
            'recipientName' => null,
        );
        return $data;
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     */
    protected function _createTrackingDetails(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $tracks = null;
        $tracking = $shipment->getAllTracks();

        if (!empty($tracking)) {
            $tracks = array();
            foreach ($tracking as $track) {
                $value = array(
                    'trackingNumbers'   => strlen($track->getNumber()) > 0 ? array($track->getNumber()) : null,
                    'carrier'           => strlen($track->getTitle()) > 0 ? $track->getTitle() : null,
                    'service'           => null,
                    'serviceType'       => null,
                );
                $tracks[] = $value;
            }
        }

        return $tracks;
    }

    private function _makeNullSafe($obj)
    {
        if(null === $obj) {
            return '';
        }
        return $obj;
    }
}
