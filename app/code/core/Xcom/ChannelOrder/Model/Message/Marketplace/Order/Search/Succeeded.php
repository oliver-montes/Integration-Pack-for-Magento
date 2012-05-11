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

class Xcom_ChannelOrder_Model_Message_Marketplace_Order_Search_Succeeded extends Xcom_Xfabric_Model_Message_Response
{

    protected function _construct()
    {
        $this->_schemaRecordName    = 'SearchMarketplaceOrderSucceeded';
        $this->_topic               = 'marketplace/order/searchSucceeded';
        $this->_schemaVersion       = '2.0.0';
        parent::_construct();
    }

    /**
     * Create/update orders once message is received
     * @return array
     */
    public function process()
    {
        parent::process();
        $data = $this->getBody();
        /** @var $helper Xcom_ChannelOrder_Helper_Data */
        $helper = Mage::helper('xcom_channelorder');
        if (empty($data['orders'])) {
            $this->logOrder($helper->__('Message does not have orders'), Xcom_Log_Model_Source_Result::RESULT_ERROR);
            return $this;
        }
        try {
            $helper->validateOrderEnvironment($data['searchMarketplaceOrder']['sellerAccountId']);
            foreach ($data['orders'] as $orderData) {
                Mage::helper('xcom_channelorder')->prepareSourceAmounts($orderData);
                /** @var $messageOrder Xcom_ChannelOrder_Model_Message_Order */
                $messageOrder   = Mage::getModel('xcom_channelorder/message_order')
                        ->setOrderMessageData($orderData)
                        ->setAccountId($data['searchMarketplaceOrder']['sellerAccountId'])
                        ->setSiteCode($data['searchMarketplaceOrder']['siteCode']);

                $channelOrder = $messageOrder->getChannelOrder();
                if ($channelOrder->getOrderId()) {
                    $messageOrder->updateOrder($orderData);
                    $this->logOrder($helper->__('Synchronization Completed. Order #%s was updated',
                            $messageOrder->getOrderNumber()),
                            Xcom_Log_Model_Source_Result::RESULT_SUCCESS);
                } else {
                    $messageOrder->createOrder($orderData);
                    $this->logOrder($helper->__('Synchronization Completed. Order #%s was created',
                            $messageOrder->getOrderNumber()),
                            Xcom_Log_Model_Source_Result::RESULT_SUCCESS);
                }
            }
        } catch(Xcom_ChannelOrder_Exception $e) {
            $this->logOrder($e->getMessage(), Xcom_Log_Model_Source_Result::RESULT_ERROR);
        } catch(Exception $e) {
            Mage::log($e->getMessage(), null, 'order_errors.log', true);
            $this->logOrder(
                $helper->__('Orders were not processed for some reason. Please contact your administrator.'),
                Xcom_Log_Model_Source_Result::RESULT_ERROR
            );
            throw $e;
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
            ->setManualType()
            ->setResult($resultType)
            ->setDescription($description)
            ->save();

        return $this;
    }

}
