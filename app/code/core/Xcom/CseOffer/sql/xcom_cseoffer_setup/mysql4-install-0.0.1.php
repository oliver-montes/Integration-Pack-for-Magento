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

$this->startSetup();

/**
 * @var $this Mage_Core_Model_Resource_Setup
 * @var $table Varien_Db_Ddl_Table
 */

/**
 * Create table 'xcom_cseoffer/channel_product'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_cseoffer/channel_product'))
    ->addColumn('channel_product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Channel Product Id')
    ->addColumn('channel_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => '0',
), 'Channel Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Product Id')
    ->addColumn('offer_status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Offer Status')
    ->addColumn('cse_item_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Cse Item Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'nullable'  => false,
), 'Created At')
    ->addColumn('link', Varien_Db_Ddl_Table::TYPE_VARCHAR, 510, array(
), 'Link')
    ->addIndex('UNQ_CHANNEL_ID_PRODUCT_ID',
    array('channel_id', 'product_id'), array('unique' => true))
    ->addIndex('IDX_XCOM_CP_CSE_ITEM_ID',
    array('cse_item_id'))
    ->addForeignKey('FK_XCOM_CSEOFFER_PRODUCT_CHANNEL_ID',
    'channel_id', $this->getTable('xcom_cse/channel'), 'channel_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom CseOffer Channel Product');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'channel_product_id',
    'int(10) unsigned NOT NULL auto_increment');

/**
 * Create table 'xcom_cseoffer/channel_history'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_cseoffer/channel_history'))
    ->addColumn('history_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'History Id')
    ->addColumn('response_result', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
), 'Response Result')
    ->addColumn('action', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
), 'Action')
    ->addColumn('channel_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Channel Id')
    ->addColumn('channel_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Channel Name')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Product Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'nullable'  => false,
), 'Created At')
    ->addColumn('log_response_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
), 'Log Response Id')
    ->addColumn('log_request_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
), 'Log Request Id')
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom CseOffer Channel History');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'history_id', 'int(10) unsigned NOT NULL auto_increment');

/**
 * Create table 'xcom_cseoffer/message_cse_offer_log_request'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_cseoffer/message_cse_offer_log_request'))
    ->addColumn('request_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'identity'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Request Id')
    ->addColumn('correlation_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
    'default'   => '',
), 'Correlation Id')
    ->addColumn('request_body', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, '64k', array(
    'nullable'  => false,
), 'Request Body')
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom CseOffer Log Request');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'request_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table 'xcom_cseoffer/message_cse_offer_log_response'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_cseoffer/message_cse_offer_log_response'))
    ->addColumn('response_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'identity'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Response Id')
    ->addColumn('correlation_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
    'default'   => '',
), 'Correlation Id')
    ->addColumn('response_body', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, '64k', array(
    'nullable'  => false,
), 'Response Body')
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom CseOffer Log Response');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'response_id', 'int(10) unsigned NOT NULL auto_increment');

$this->endSetup();
