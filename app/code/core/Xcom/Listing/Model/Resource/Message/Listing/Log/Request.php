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

class Xcom_Listing_Model_Resource_Message_Listing_Log_Request extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('xcom_listing/message_listing_log_request', 'request_id');
    }

    /**
     * @return string
     */
    public function getLastCorrelationId()
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('correlation_id'))
            ->order('request_id DESC')
            ->limit(1);
        return $adapter->fetchOne($select);
    }

    /**
     * @param int $correlationId
     * @return string
     */
    public function getRequestBodyByCorrelationId($correlationId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('request_body'))
            ->where('correlation_id=?', $correlationId)
            ->order('request_id DESC')
            ->limit(1);
        return $adapter->fetchOne($select);
    }
}
