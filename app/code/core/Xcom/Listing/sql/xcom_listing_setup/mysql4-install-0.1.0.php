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

$this->startSetup();

/**
 * @var $this Mage_Core_Model_Resource_Setup
 * @var $table Varien_Db_Ddl_Table
 */


/**
 * Create table 'xcom_listing/category'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_listing/category'))
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Category Id')
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Name')
    ->addColumn('path', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Path')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Parent Id')
    ->addColumn('leaf_category', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'nullable'  => false,
    'default'   => '0',
), 'Leaf Category')
    ->addColumn('level', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'default'   => '0',
), 'Level')
    ->addColumn('children_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'nullable'  => false,
), 'Children Count')
    ->addColumn('catalog_enabled', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'nullable'  => false,
    'default'   => '0',
), 'Catalog Enabled')
    ->addColumn('site_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Site Code')
    ->addColumn('marketplace', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Marketplace')
    ->addColumn('environment_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
    'nullable'  => false,
), 'Environment Name')
    ->addIndex('IDX_CATEGORY_NAME', array('name'), array())
    ->addIndex('IDX_CATEGORY_LEVEL', array('level'), array())
    ->addIndex('IDX_CATEGORY_PARENT_ID', array('parent_id'), array())
    ->addIndex('UNQ_MARKETPLACE_ENV_SITECODE_ID',
        array('marketplace', 'environment_name', 'site_code', 'id'), array('unique' => true))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Listing Category');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'category_id', 'int(10) unsigned NOT NULL auto_increment');

/**
 * Create table 'xcom_listing/category_product_type'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_listing/category_product_type'))
    ->addColumn('relation_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Category Id')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Category Id')
    ->addColumn('mapping_product_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Mapping Product Type Id')
    ->addIndex('UNQ_CTGR_PRDCT_TYPE_ID',
        array('category_id', 'mapping_product_type_id'), array('unique' => true))
    ->addIndex('FK_MAPPING_PRODUCT_TYPE_ID',
        array('mapping_product_type_id'))
    ->addForeignKey('FK_MAPPING_PRODUCT_TYPE_ID',
        'mapping_product_type_id', $this->getTable('xcom_mapping/product_type'), 'mapping_product_type_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey('FK_XCOM_LISTING_CATEGORY_ID',
        'category_id', $this->getTable('xcom_listing/category'), 'category_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Listing Category Product Type');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'relation_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table 'xcom_listing/listing'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_listing/listing'))
    ->addColumn('listing_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Listing Id')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'default'   => '0',
), 'Category Id')
    ->addColumn('price_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Price Type')
    ->addColumn('price_value', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12,4), array(
    'nullable'  => false,
    'default'   => '0.0000',
), 'Price Value')
    ->addColumn('price_value_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Price Value Type')
    ->addColumn('qty_value', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12,4), array(
    'nullable'  => false,
), 'Qty Value')
    ->addColumn('qty_value_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
    'default'   => '0.0000',
), 'Qty Value Type')
    ->addColumn('policy_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'default'   => '0',
), 'Policy Id')
    ->addIndex('FK_XCOM_LISTING_POLICY_ID',
    array('policy_id'))
    ->addForeignKey('FK_XCOM_LISTING_POLICY_ID',
    'policy_id', $this->getTable('xcom_mmp/policy'), 'policy_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Listing');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'listing_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table 'xcom_listing/channel_product'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_listing/channel_product'))
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
    ->addColumn('listing_status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Listing Status')
    ->addColumn('market_item_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Market Item Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'nullable'  => false,
), 'Created At')
    ->addColumn('listing_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => '0',
), 'Listing Id')
    ->addColumn('link', Varien_Db_Ddl_Table::TYPE_VARCHAR, 510, array(
), 'Link')
    ->addIndex('UNQ_CHANNEL_ID_PRODUCT_ID',
    array('channel_id', 'product_id'), array('unique' => true))
    ->addIndex('FK_XCOM_CHANNEL_PRODUCT_LISTING_ID',
    array('listing_id'))
    ->addIndex('IDX_XCOM_CP_MARKET_ITEM_ID',
    array('market_item_id'))
    ->addForeignKey('FK_XCOM_LISTING_PRODUCT_CHANNEL_ID',
    'channel_id', $this->getTable('xcom_mmp/channel'), 'channel_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey('FK_XCOM_CHANNEL_PRODUCT_LISTING_ID',
    'listing_id', $this->getTable('xcom_listing/listing'), 'listing_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Listing Channel Product');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'channel_product_id',
    'int(10) unsigned NOT NULL auto_increment');



/**
 * Create table 'xcom_listing/channel_history'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_listing/channel_history'))
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
    ->addColumn('category', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Category')
    ->addColumn('policy', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Policy')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Product Id')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12,4), array(
    'nullable'  => false,
    'default'   => '0.0000',
), 'Qty')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12,4), array(
    'nullable'  => false,
    'default'   => '0.0000',
), 'Price')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'nullable'  => false,
), 'Created At')
    ->addColumn('log_response_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
), 'Log Response Id')
    ->addColumn('log_request_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
), 'Log Request Id')
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Channel History');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'history_id', 'int(10) unsigned NOT NULL auto_increment');



/**
 * Create table 'xcom_listing/message_listing_log_request'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_listing/message_listing_log_request'))
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
    ->setOption('comment', 'Xcom Listing Log Request');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'request_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table 'xcom_listing/message_listing_log_response'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_listing/message_listing_log_response'))
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
    ->setOption('comment', 'Xcom Listing Log Response');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'response_id', 'int(10) unsigned NOT NULL auto_increment');

$this->endSetup();
