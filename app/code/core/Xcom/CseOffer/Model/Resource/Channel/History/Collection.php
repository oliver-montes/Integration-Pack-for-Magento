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

class Xcom_CseOffer_Model_Resource_Channel_History_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initialize resource model for collection.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('xcom_cseoffer/channel_history');
    }

    /**
     * Add channel type filter to the result
     *
     * @param $code
     * @return Xcom_CseOffer_Model_Resource_Channel_History_Collection
     */
    public function addChannelTypeFilter($code)
    {
        $this->getSelect()
            ->joinInner(array('channel' => $this->getTable('xcom_cse/channel')),
                'channel.channel_id = main_table.channel_id AND ' .
                $this->getConnection()->quoteInto('channel.channeltype_code = ?', $code)
            );

        return $this;
    }

    /**
     * Add filter by channel
     *
     * @param  $code
     * @return Xcom_CseOffer_Model_Resource_Channel_History_Collection
     */
    public function addChannelFilter($code)
    {
        $this->getSelect()
            ->where('main_table.channel_id = :channel_id');
        $this->addBindParam('channel_id', $code);
        return $this;
    }
}
