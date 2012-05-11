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

class Xcom_CseOffer_Model_Message_Cse_Offer_Request extends Xcom_Xfabric_Model_Message_Request
{
    protected $_action = null;

    /**
     * Prepare data before sending
     * @param Varien_Object $dataObject
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    protected function _prepareData(Varien_Object $dataObject = null)
    {
        $this->addCorrelationId();

        $logRequest = $this->_saveLogRequestBody();
        $this->_prepareChannelHistory($logRequest, $dataObject);

        return parent::_prepareData($dataObject);
    }

    /**
     * @return Xcom_CseOffer_Model_Message_Offer_Log_Request
     */
    protected function _saveLogRequestBody()
    {
        $model = Mage::getModel('xcom_cseoffer/message_cse_offer_log_request')
            ->setCorrelationId($this->getCorrelationId())
            ->setRequestBody(json_encode($this->getMessageData()))
            ->save();
        return $model;
    }

    /**
     * @param Varien_Object $logRequest
     * @param Varien_Object $dataObject
     * @return Xcom_CseOffer_Model_Message_Offer_Create_Request
     */
    protected function _prepareChannelHistory(Varien_Object $logRequest, Varien_Object $dataObject)
    {
        foreach($dataObject->getData('products') as $product) {
            Mage::getModel('xcom_cseoffer/channel_history')
                ->addData(array(
                    'channel_id'        => $dataObject->getData('channel')->getId(),
                    'channel_name'      => $dataObject->getData('channel')->getName(),
                    'product_id'        => $product->getId(),
                    'response_result'   => Xcom_CseOffer_Model_Channel_Product::STATUS_PENDING,
                    // TODO create, update, search, cancel
                    'action'            => $this->_action,
                    'log_request_id'    => $logRequest->getId(),
                ))
                ->save();
        }
        return $this;
    }
}
