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
 * @package     Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Xfabric_Model_Message
{
    const AVRO_BINARY_ENCODING = 'binary';
    const AVRO_JSON_ENCODING = 'json';
    const SCHEMA_VERSION_HEADER = 'X-XC-SCHEMA-VERSION';
    const SCHEMA_URI_HEADER = 'X-XC-SCHEMA-URI';
    const CORRELATION_ID_HEADER = 'X-XC-RESULT-CORRELATION-ID';
    const DESTINATION_ID_HEADER = 'X-XC-DESTINATION-ID';
    const PUBLISHER_PSEUDONYM_HEADER = 'X-XC-PUBLISHER-PSEUDONYM';
    const CONTENT_TYPE_HEADER = 'Content-Type';
    const AUTHORIZATION_HEADER = 'Authorization';

    protected $_headers;

    protected $_topic;

    protected $_body;

    protected $_messageData;

    public function __construct($options)
    {
        if (isset($options['headers'])) {
            foreach ($options['headers'] as $key => $value) {
                $this->_headers[strtoupper($key)] = $value;
            }
        }
        if (isset($options['body'])) {
            $this->_body = $options['body'];
        }
        if (isset($options['topic'])) {
            $this->_topic = $options['topic'];
        }
        if (isset($options['message_data'])) {
            $this->_messageData = $options['message_data'];
        }
    }

    public function getBody()
    {
        return $this->_body;
    }

    public function getHeaders()
    {
        return $this->_headers;
    }

    public function getHeader($headerName)
    {
        if (!empty($this->_headers[strtoupper($headerName)])) {
            return trim($this->_headers[strtoupper($headerName)]);
        }
        return null;
    }

    public function getTopic()
    {
        return $this->_topic;
    }

    public function getCorrelationId()
    {
        return $this->getHeader(self::CORRELATION_ID_HEADER);
    }

    public function getSchemaUri()
    {
        return $this->getHeader(self::SCHEMA_URI_HEADER);
    }

    public function getMessageData()
    {
        return $this->_messageData;
    }

    public function getAuthorization()
    {
        return $this->getHeader(self::AUTHORIZATION_HEADER);
    }
}
