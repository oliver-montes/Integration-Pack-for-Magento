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
/** @var $adapter Varien_Db_Adapter_Pdo_Mysql */
$adapter = $this->getConnection();
$channelGridTable = $this->getTable('xcom_channelorder/channel_grid');
$adapter->addColumn($channelGridTable, 'order_number', "VARCHAR(32) NOT NULL DEFAULT ''");
$adapter->addColumn($channelGridTable, 'source', "VARCHAR(32) NOT NULL DEFAULT ''");
$adapter->addColumn($channelGridTable, 'source_id', "VARCHAR(32) NOT NULL DEFAULT ''");
$adapter->addColumn($channelGridTable, 'account_name', "VARCHAR(255) NOT NULL DEFAULT ''");
$adapter->addColumn($channelGridTable, 'status', "VARCHAR(255) NOT NULL DEFAULT ''");

/**
 * Create table xcom_order_channel_payment
 */
$table = $adapter
    ->newTable($this->getTable('xcom_channelorder/channel_payment'))
    ->addColumn('channel_payment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Payment Id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Order Id')
    ->addColumn('method', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
    'default'   => '',
), 'Payment Method')
    ->addColumn('currency', Varien_Db_Ddl_Table::TYPE_VARCHAR, 3, array(
    'nullable'  => false,
    'default'   => '',
), 'Currency')
    ->addColumn('amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12,4), array(
    'default'   => '0.0000',
), 'Cost')
    ->addColumn('fee', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12,4), array(
    'default'   => '0.0000',
), 'Fee')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'nullable'  => false,
), 'Payment Date')
    ->addColumn('external_transaction_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
    'nullable'  => false,
    'default'   => '',
), 'External Transaction Id')
    ->addColumn('external_transaction_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'nullable'  => false,
), 'External Transaction Date')
    ->addIndex('IDX_XCOM_ORDER_CHANNEL_P_SALES_ORDER_ID',
        array('order_id'))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Order Channel Payment');
$adapter->createTable($table);

$adapter->modifyColumn($table->getName(), 'channel_payment_id', 'int(10) unsigned NOT NULL auto_increment');

/**
 * Create table xcom_order_channel_item
 */
$table = $adapter
    ->newTable($this->getTable('xcom_channelorder/channel_item'))
    ->addColumn('channel_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Channel Item Id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Order Id')
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Item Id')
    ->addColumn('offer_url', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
    'default'   => '',
), 'Offer Url')
    ->addColumn('item_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
    'default'   => '',
), 'Item Number')
    ->addIndex('IDX_XCOM_ORDER_CHANNEL_I_SALES_ORDER_ID', array('order_id'))
    ->addIndex('IDX_XCOM_ORDER_CHANNEL_I_SALES_ITEM_ID', array('item_id'))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Order Channel Items');
$adapter->createTable($table);

$adapter->modifyColumn($table->getName(), 'channel_item_id', 'int(10) unsigned NOT NULL auto_increment');

$adapter->dropForeignKey($this->getTable('xcom_channelorder/channel_grid'), 'FK_XCOM_ORDER_CHANNEL_SALES_ORDER_ID');

$this->endSetup();
