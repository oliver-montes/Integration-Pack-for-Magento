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
 * @package     Xcom_Mmp
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Mmp_Model_Message_Marketplace_Authorization_Init_Request extends Xcom_Xfabric_Model_Message_Request
{
    protected function _construct()
    {
        parent::_construct();
        $this->_schemaRecordName = 'InitAuthorization';
        $this->_topic = 'marketplace/authorization/init';
        $this->setIsWaitResponse();
    }

    /**
     * Prepare message data.
     *
     * @param null|Varien_Object $dataObject
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    public function _prepareData(Varien_Object $dataObject = null)
    {
        $this->addCorrelationId();
        $this->setMessageData(array(
            'returnURL' => $dataObject->getReturnUrl(),
            'cancelURL' => $dataObject->getCancelUrl(),
            'environmentName' => $dataObject->getEnvironmentName(),
            'guid' => $dataObject->getGuid(),
            'userMarketplaceId' => $dataObject->getUserMarketplaceId()
        ));
        return parent::_prepareData($dataObject);
    }

    /**
     * The method fakeBeforeSend() is calling only from send() method
     * of Xcom_Stub_Model_Transport_Stub module.
     * So, inside fakeBeforeSend() we always know that it is Stub transport model.
     *
     * @return Xcom_Mmp_Model_Message_Marketplace_Authorization_Init_Request
     */
    public function fakeBeforeSend()
    {
        /**
         * Exception (for Stub transport model):
         *   Set fake GUID = 559d8e99-THIS-CAN-BE-ANY-GUID-STRING-UNIQUE-TO-YOU-EBAY,
         *   Set fake CorrelationId = md5(559d8e99-THIS-CAN-BE-ANY-GUID-STRING-UNIQUE-TO-YOU-EBAY),
         *   Set fake X-XC-RESULT-CORRELATION-ID header = md5(559d8e99-THIS-CAN-BE-ANY-GUID-STRING-UNIQUE-TO-YOU-EBAY).
         */
        $messageData = $this->getMessageData();
        if (!empty($messageData['guid'])) {
            $messageData['guid'] = '559d8e99-THIS-CAN-BE-ANY-GUID-STRING-UNIQUE-TO-YOU-EBAY';
            $this->setMessageData($messageData);
        }
        $this->setCorrelationId(md5('559d8e99-THIS-CAN-BE-ANY-GUID-STRING-UNIQUE-TO-YOU-EBAY'));
        $this->addHeader("X-XC-RESULT-CORRELATION-ID", $this->getCorrelationId());
        $this->encode();
        return $this;
    }
}
