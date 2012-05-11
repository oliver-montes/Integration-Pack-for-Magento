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
 * @package     Xcom_Listing
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Listing_Model_Message_Listing_Request extends Xcom_Xfabric_Model_Message_Request
{
    protected $_action = null;
    protected $_categoryName;

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
     * @return Xcom_Listing_Model_Message_Listing_Log_Request
     */
    protected function _saveLogRequestBody()
    {
        $model = Mage::getModel('xcom_listing/message_listing_log_request')
            ->setCorrelationId($this->getCorrelationId())
            ->setRequestBody(json_encode($this->getMessageData()))
            ->save();
        return $model;
    }

    /**
     * @param Varien_Object $logRequest
     * @param Varien_Object $dataObject
     * @return Xcom_Listing_Model_Message_Listing_Create_Request
     */
    protected function _prepareChannelHistory(Varien_Object $logRequest, Varien_Object $dataObject)
    {
        foreach($dataObject->getData('products') as $product) {
            if (!($product instanceof Varien_Object)) {
                continue;
            }
            Mage::getModel('xcom_listing/channel_history')
                ->addData(array(
                    'channel_id'        => $dataObject->getData('channel')->getId(),
                    'channel_name'      => $dataObject->getData('channel')->getName(),
                    'policy'            => $dataObject->getData('policy')->getName(),
                    'category'          => $this->_getCategoryName(
                                               $product->getListingCategoryId(),
                                               $dataObject->getData('channel')),
                    'product_id'        => $product->getId(),
                    'qty'               => $product->getListingQty(),
                    'price'             => $product->getListingPrice(),
                    'response_result'   => Xcom_Listing_Model_Channel_Product::STATUS_PENDING,
                    // TODO create, update, search, cancel
                    'action'            => $this->_action,
                    'log_request_id'    => $logRequest->getId(),
                ))
                ->save();
        }
        return $this;
    }

    /**
     * Get category name by id
     *
     * @param $categoryId
     * @param $channel
     * @return mixed
     */
    protected function _getCategoryName($categoryId, $channel)
    {
        return Mage::getResourceSingleton('xcom_listing/category')
            ->getNameFromCategory($categoryId, $channel->getChanneltypeCode(),
                $channel->getSiteCode(), $channel->getAccountId());
    }
}
