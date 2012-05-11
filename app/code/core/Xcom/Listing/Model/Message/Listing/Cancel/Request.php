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

class Xcom_Listing_Model_Message_Listing_Cancel_Request extends Xcom_Listing_Model_Message_Listing_Request
{
    protected function _construct()
    {
        parent::_construct();
        $this->_schemaRecordName = 'CancelListing';
        $this->_topic = 'listing/cancel';
        $this->_action = 'remove';
    }

    /**
     * Prepare data before sending
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    public function _prepareData(Varien_Object $dataObject = null)
    {
        $this->_channelProducts = $dataObject->getChannelProducts();
        $this->_channel = $dataObject->getChannel();

        $this->setMessageData(
            array(
                'skus'       => $dataObject->getSkus(),
                'xProfileId' => $dataObject->getPolicyId()
            )
        );
        $this->addCorrelationId();
        $this->_saveLogRequestBody();
        $this->_hasDataChanges = true;

        return $this;
    }

}
