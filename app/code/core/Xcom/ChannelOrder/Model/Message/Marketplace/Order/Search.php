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

class Xcom_ChannelOrder_Model_Message_Marketplace_Order_Search extends Xcom_Xfabric_Model_Message_Request
{
    protected function _construct()
    {
        $this->_schemaRecordName = 'SearchMarketplaceOrder';
        $this->_topic = 'marketplace/order/search';
        parent::_construct();
    }

    /**
     * Prepare message data.
     *
     * @param null|Varien_Object $options
     * @return Xcom_Xfabric_Model_Message_Request
     */
    protected function _prepareData(Varien_Object $options = null)
    {
        $this->setMessageData(array(
            'siteCode' => $options->getSiteCode(),
            'sellerAccountId'   => $options->getSellerAccountId(),
            'query' => array(
                'fields' => $options->getFields(),
                'predicates' => array(
                    array(
                        'field' => 'dateOrdered',
                        'operator'  => "GREATER_THAN_EQUALS",
                        'values'    => array($options->getFromDate())), // format "2012-03-01T15:37:02+00:00"
                    array(
                        'field' => 'dateOrdered',
                        'operator'  => "LESS_THAN_EQUALS",
                        'values'    => array($options->getToDate())), // format "2012-03-01T15:37:02+00:00"
                    array(
                        'field' => 'sourceId',
                        'operator'  => "EQUALS",
                        'values'    => array($options->getSourceId()))
                ),
                'ordering' => $options->getOrdering()
            )
        ));
        return parent::_prepareData($options);
    }
}
