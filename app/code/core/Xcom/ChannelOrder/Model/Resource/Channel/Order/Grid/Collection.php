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

class Xcom_ChannelOrder_Model_Resource_Channel_Order_Grid_Collection
    extends Mage_Sales_Model_Mysql4_Order_Grid_Collection
{
    protected function _initSelect()
    {
        parent::_initSelect()->getSelect()
            ->joinLeft(array('xoco' => $this->getTable('xcom_channelorder/channel_grid')),
            'main_table.entity_id = xoco.order_id',
            array('channel_id'   => 'xoco.channel_id'));
        return $this;
    }

    /**
     * Add channel filter to select by id.
     *
     * @param int $channelId
     * @return Xcom_ChannelOrder_Model_Resource_Channel_Order_Grid_Collection
     */
    public function addChannelIdFilter($channelId)
    {
        if ($channelId) {
            $this->addFieldToFilter('`xoco`.`channel_id`', array('eq' => (int)$channelId));
        } else {
            $this->addFieldToFilter('`xoco`.`channel_id`', array('null' => ''));
        }
        return $this;
    }
}
