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

class Xcom_Listing_Model_Message_Listing_Search_Failed extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Init message object
     */
    protected function _construct()
    {
        $this->_schemaRecordName = 'SearchListingFailed';
        $this->_topic = 'listing/searchFailed';
        parent::_construct();
    }

    /**
     * @param array $data
     * @return array
     */
    public function process()
    {
        parent::process();
        $data = $this->getBody();
        //@TODO: Update error log for search message instead
        /*if (!empty($data['filter']['skus']) && !empty($data['xProfileId'])) {
            $channel = $this->_getChannelFromPolicy($data['xProfileId']);
            if ($channel) {
                $this->_updateProductStatus($channel, $data['filter']['skus'],
                    Xcom_Listing_Model_Channel_Product::STATUS_FAILURE);
            }
        }*/

        return $this;
    }
}
