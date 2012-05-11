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

class Xcom_ChannelOrder_Model_Message_Marketplace_Order_Search_Failed extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Init object
     */
    protected function _construct()
    {
        $this->_schemaRecordName = 'SearchMarketplaceOrderFailed';
        $this->_topic = 'marketplace/order/searchFailed';
        parent::_construct();
    }

    /**
     * Process data on message received
     *
     * @return array
     */
    public function process()
    {
        parent::process();
        $data = $this->getBody();
        if (isset($data['errors']) && !empty($data['errors'])) {
            $description = '';
            foreach ($data['errors'] as $error) {
                if (isset($error{'code'}) && isset($error{'message'})) {
                    $description .= sprintf("(%s) %s; ", $error{'code'}, $error{'message'});
                }
            }
            $this->logOrder($description, Xcom_Log_Model_Source_Result::RESULT_ERROR);
        }
        return $this;
    }

    /**
     * Log the result of order creation
     *
     * @param string $description
     * @param string $result Xcom_Log_Model_Source_Result constant
     * @return Xcom_ChannelOrder_Model_Message_Marketplace_Order_Search_Failed
     */
    public function logOrder($description, $result)
    {
        $description = sprintf("Topic /%s: %s", $this->getTopic(), $description);
        Mage::getModel('xcom_log/log')
            ->setManualType()
            ->setResult($result)
            ->setDescription($description)
            ->save();
        return $this;
    }
}
