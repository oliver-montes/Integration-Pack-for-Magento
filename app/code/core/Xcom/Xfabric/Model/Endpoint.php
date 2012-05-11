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
 * @package    Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Xfabric_Model_Endpoint
{
    /** @var Xcom_Xfabric_Model_Message_Interface */
    protected $_messageData = array();

    /** @var Xcom_Xfabric_Model_Transport_Interface */
    protected $_transport = null;

    /** @var Xcom_Xfabric_Model_Authorization_Interface */
    protected $_authorization = null;

    /** @var Xcom_Xfabric_Model_Schema_Interface */
    protected $_schema = null;

    /** @var Xcom_Xfabric_Model_Encoder_Interface */
    protected $_encoder = null;

    /** @var string */
    protected $_encoding = null;

    public function __construct($options)
    {
        if (isset($options['message_data'])) {
            if (!$options['message_data'] instanceof Xcom_Xfabric_Model_Message_Data_Interface) {
                throw new Exception('Message data should be an instance of Xcom_Xfabric_Model_Message_Data_Interface');
            }
            $this->_messageData = $options['message_data'];
        }

        if (isset($options['transport'])) {
            if (!$options['transport'] instanceof Xcom_Xfabric_Model_Transport_Interface) {
                throw new Exception('Transport should be an instance of Xcom_Xfabric_Model_Transport_Interface');
            }
            $this->_transport = $options['transport'];
        }

        if (isset($options['authorization'])) {
            if (!$options['authorization'] instanceof Xcom_Xfabric_Model_Authorization_Interface) {
                throw new Exception('Authorization should be an instance of '
                    . 'Xcom_Xfabric_Model_Authorization_Interface');
            }
            $this->_authorization = $options['authorization'];
        } else {
            throw new Exception('Authorization should be set');
        }

        if (isset($options['schema'])) {
            if (!$options['schema'] instanceof Xcom_Xfabric_Model_Schema_Interface) {
                throw new Exception('Schema should be an instance of '
                    . 'Xcom_Xfabric_Model_Schema_Interface');
            }
            $this->_schema = $options['schema'];
        }

        if (isset($options['encoder'])) {
            if (!$options['encoder'] instanceof Xcom_Xfabric_Model_Encoder_Interface) {
                throw new Exception('Encoder should be an instance of '
                    . 'Xcom_Xfabric_Model_Encoder_Interface');
            }
            $this->_encoder = $options['encoder'];
        } else {
            throw new Exception('Encoder should be set');
        }

        if (isset($options['encoding'])) {
            $this->_encoding = $options['encoding'];
        } else {
            throw new Exception('Encoding should be set');
        }
    }

    public function send()
    {
        if (!$this->_messageData instanceof Xcom_Xfabric_Model_Message_Data_Interface) {
            throw new Exception('Message data should be set');
        }
        $options = $this->_messageData->getOptions();
        $topic = $options['topic'];
        $messageData = $this->_messageData->getMessageData();

        $messageBody = $this->_encoder->encodeText($messageData, $this->_schema->getRawSchema());
        $messageOptions = array(
            'body' => $messageBody,
            'headers' => $this->_getMessageHeaders(),
            'topic' => $topic
        );

        $message = Mage::getModel('xcom_xfabric/message', $messageOptions);
        $messageOptions = $this->_messageData->getOptions();
        $options = array(
            'message_data' => json_encode($messageData),
            'synchronous' => isset($messageOptions['synchronous']) ? true : false
        );
        return $this->_transport->sendMessage($message, $options);
    }

    public function receive()
    {
        $messageDataOptions = $this->_messageData->getOptions();
        $messageOptions = array(
            'body' => $messageDataOptions['body'],
            'headers' => $messageDataOptions['headers'],
            'topic' => $messageDataOptions['topic'],
            'message_data' => $this->_encoder->decodeText($messageDataOptions['body'],
                $this->_schema->getRawSchema())
        );

        $this->_message = Mage::getModel('xcom_xfabric/message', $messageOptions);
        $this->validateAuthorizationHeader($this->_message->getAuthorization());

        $response = Mage::getModel('xcom_xfabric/message_response')
            ->setBody($this->_message->getMessageData())
            ->setHeaders($this->_message->getHeaders())
            ->setTopic($this->_message->getTopic())
            ->setCorrelationId($this->_message->getCorrelationId())
            ->setDataChanges(true)
            ->save();

        Mage::dispatchEvent('response_message_received', array('message' => $this->_message));
        if (!$messageDataOptions['is_process_later']) {
            /*general event*/
            Mage::dispatchEvent('response_message_process',
                array('message' => $this->_message,
                    'message_id' => $response->getId()  /* @deprecated backward compatibility with Messaging Framework 0.0.1 */
                ));

            /*topic related event*/
            $eventName = 'response_message_process_' . str_replace('/', '_', $this->_message->getTopic());
            Mage::dispatchEvent($eventName, array('message' => $this->_message));
        }
    }

    public function getMessage()
    {
        return $this->_message;
    }

    public function validateAuthorizationHeader($authorization)
    {
        if (empty($authorization)) {
            throw Mage::exception('Xcom_Xfabric', $this->__('Response Message does not have Authorization header'));
        }
        if (!$this->_authorization->hasAuthorizationData()) {
            throw Mage::exception('Xcom_Xfabric',
                $this->__('X.commerce Fabric Bearer Token must be filled in system configurations'));
        }
        if ($this->_authorization->getFabricData('token') !== $authorization) {
            throw Mage::exception('Xcom_Xfabric', $this->__('Authorization header is wrong'));
        }
        return true;
    }

    protected function _getMessageHeaders()
    {
        $messageOptions = $this->_messageData->getOptions();
        $headers = array();
        if (isset($messageOptions['correlation_id'])) {
            $headers[Xcom_Xfabric_Model_Message::CORRELATION_ID_HEADER] = $messageOptions['correlation_id'];
        } else if (isset($messageOptions['synchronous'])) {
            $headers[Xcom_Xfabric_Model_Message::CORRELATION_ID_HEADER] = $this->_getUid();
        }
        if (isset($messageOptions['destination_id'])) {
            $headers[Xcom_Xfabric_Model_Message::DESTINATION_ID_HEADER] = $messageOptions['destination_id'];
        }
        if ($this->_encoding == Xcom_Xfabric_Model_Message::AVRO_BINARY_ENCODING) {
            $headers[Xcom_Xfabric_Model_Message::CONTENT_TYPE_HEADER] = 'avro/binary';
        } else if ($this->_encoding == Xcom_Xfabric_Model_Message::AVRO_JSON_ENCODING) {
            $headers[Xcom_Xfabric_Model_Message::CONTENT_TYPE_HEADER] = 'application/json';
        }
        if (isset($messageOptions['on_behalf_of_tenant']) && !$messageOptions['on_behalf_of_tenant']) {
            $headers[Xcom_Xfabric_Model_Message::AUTHORIZATION_HEADER] = $this->_authorization->getSelfData('token');
        } else {
            $headers[Xcom_Xfabric_Model_Message::AUTHORIZATION_HEADER] = $this->_authorization->getBearerData('token');
        }
        if (isset($messageOptions['schema_version'])) {
            $headers[Xcom_Xfabric_Model_Message::SCHEMA_VERSION_HEADER] = $messageOptions['schema_version'];
        }
        $headers[Xcom_Xfabric_Model_Message::SCHEMA_URI_HEADER] = $this->_schema->getSchemaUri();
        return $headers;
    }

    protected final function _getUid()
    {
        return md5(uniqid(mt_rand(), true));
    }
}
