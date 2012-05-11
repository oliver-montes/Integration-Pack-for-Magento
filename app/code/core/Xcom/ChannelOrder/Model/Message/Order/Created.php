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

class Xcom_ChannelOrder_Model_Message_Order_Created
    extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Customer data
     *
     * @var array
     */
    protected $_customerData;

    protected $_schemaVersion = '3.0.0';

    protected $_orderNumber;


    /**
     * Message object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_schemaRecordName = 'OrderCreated';
        $this->_topic = 'order/created';
        parent::_construct();
    }

    /**
     * Create Order
     *
     * @return Xcom_ChannelOrder_Model_Message_Order_Created
     */
    public function process()
    {
        parent::process();
        $data = $this->getBody();
        try {
            $this->_orderNumber = $data['order']['orderNumber'];

            /** @var $helper Xcom_ChannelOrder_Helper_Data */
            $helper = Mage::helper('xcom_channelorder');
            $helper->validateOrderEnvironment($data['accountId']);
            /** @var $order Xcom_ChannelOrder_Model_Message_Order */
            $order = Mage::getModel('xcom_channelorder/message_order');

            $order->setOrderMessageData($data['order'])
                ->setAccountId($data['accountId'])
                ->setSiteCode($data['siteCode'])
                ->createOrder($data['order']);
            $this->logOrder($helper->__('Order #%s was created', $this->_orderNumber),
                Xcom_Log_Model_Source_Result::RESULT_SUCCESS);
        } catch(Xcom_ChannelOrder_Exception $ex) {
            $this->logOrder($ex->getMessage(), Xcom_Log_Model_Source_Result::RESULT_ERROR);
        } catch(Exception $ex) {
            Mage::log($ex->getMessage(), null, 'order_errors.log', true);
            $this->logOrder($helper->__('Order was not created for some reason. Please contact your administrator.'),
                Xcom_Log_Model_Source_Result::RESULT_ERROR);
        }
        return $this;
    }

    /**
     * Log the result of order creation
     *
     * @param string $description
     * @param string $resultType
     *
     * @return Xcom_ChannelOrder_Model_Message_Order_Created
     */
    public function logOrder($description, $resultType)
    {
        $description = sprintf("Topic /%s: %s", $this->getTopic(), $description);
        Mage::getModel('xcom_log/log')
            ->setAutomaticType()
            ->setResult($resultType)
            ->setDescription($description)
            ->save();

        return $this;
    }
}
