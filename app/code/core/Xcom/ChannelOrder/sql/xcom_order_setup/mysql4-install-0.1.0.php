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

/** @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

/**
 * Create table xcom_order_channel_grid
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_channelorder/channel_grid'))
    ->addColumn('channel_order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Channel Order Id')
    ->addColumn('channel_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Channel Id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Order Id')
    ->addIndex('UNQ_CHANNEL_ID_ORDER_ID',
        array('channel_id', 'order_id'), array('unique' => true))
    ->addIndex('IDX_XCOM_ORDER_CHANNEL_SALES_ORDER_ID',
        array('order_id'))
    ->addForeignKey('FK_XCOM_ORDER_CHANNEL_CHANNEL_ID',
        'channel_id', $this->getTable('xcom_mmp/channel'), 'channel_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->addForeignKey('FK_XCOM_ORDER_CHANNEL_SALES_ORDER_ID',
        'order_id', $this->getTable('sales/order_grid'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Order Channel Order');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'channel_order_id', 'int(10) unsigned NOT NULL auto_increment');

$this->endSetup();
