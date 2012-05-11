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
 * @package     Xcom_Mmp
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$this->startSetup();

/**
 * @var $this Mage_Core_Model_Resource_Setup
 * @var $table Varien_Db_Ddl_Table
 */

/**
 * Create table 'xcom_mmp/channel'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mmp/channel'))
    ->addColumn('channel_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Channel Id')
    ->addColumn('channeltype_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
    'nullable'  => false,
    'default'   => '',
), 'Channeltype Code')
    ->addColumn('site_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
    'nullable'  => false,
    'default'   => '',
), 'Site Code')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
    'nullable'  => false,
    'default'   => '',
), 'Code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
    'default'   => '',
), 'Name')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Store Id')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'nullable'  => false,
    'default'   => '0',
), 'Sort Order')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'nullable'  => false,
    'default'   => '0',
), 'Is Active')
    ->addColumn('account_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => '0',
), 'Account Id')
    ->addIndex('account_id',
    array('account_id'))
    ->addForeignKey('FK_XCOM_MMP_CHANNEL_ACCOUNT_ID',
    'account_id', $this->getTable('xcom_mmp/account'), 'account_id',
    Varien_Db_Ddl_Table::ACTION_NO_ACTION, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->addForeignKey('FK_XCOM_MMP_CHANNEL_STORE_ID',
    'store_id', $this->getTable('core/store'), 'store_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Channel');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'channel_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table 'xcom_mmp/policy'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mmp/policy'))
    ->addColumn('policy_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Policy Id')
    ->addColumn('channel_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
), 'Channel Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Name')
    ->addColumn('xprofile_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Xprofile Id')
    ->addColumn('correlation_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
), 'Correlation Id')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'nullable'  => false,
    'default'   => '0',
), 'Is Active')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'nullable'  => false,
    'default'   => '1',
), 'Status')
    ->addIndex('UNQ_XCOM_MMP_CHANNEL_POLICY_CHANNEL_ID_NAME',
    array('channel_id', 'name'), array('unique' => true))
    ->addIndex('UNQ_XCOM_MMP_CHANNEL_POLICY_CORRELATION_ID',
    array('correlation_id'), array('unique' => true))
    ->addForeignKey('FK_XCOM_MMP_CHANNEL_POLICY_CHANNEL_ID',
    'channel_id', $this->getTable('xcom_mmp/channel'), 'channel_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Channel Policy');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'policy_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table 'xcom_mmp/shipping_service'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mmp/shipping_service'))
    ->addColumn('shipping_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Shipping Id')
    ->addColumn('channel_type_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Channel Type Code')
    ->addColumn('site_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Site Code')
    ->addColumn('environment', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
    'nullable'  => false,
    'default'   => '',
), 'Environment')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Description')
    ->addColumn('carrier', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Carrier')
    ->addColumn('service_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Service Name')
    ->addColumn('shipping_time_max', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Shipping Time Max')
    ->addColumn('shipping_time_min', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Shipping Time Min')
    ->addColumn('rate_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Rate Type')
    ->addColumn('dimensions_required', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => '0',
), 'Dimensions Required')
    ->addColumn('weight_required', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => '0',
), 'Weight Required')
    ->addColumn('surcharge_applicable', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => '0',
), 'Surcharge Applicable')
    ->addIndex('UNQ_CHANNEL_CODE_SERVICE_NAME',
    array('channel_type_code', 'site_code', 'service_name', 'environment'), array('unique' => true))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Core Shipping Service');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'shipping_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table 'xcom_mmp/policy_shipping'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mmp/policy_shipping'))
    ->addColumn('policy_shipping_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Policy Shipping Id')
    ->addColumn('policy_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Policy Id')
    ->addColumn('shipping_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Shipping Id')
    ->addColumn('cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12,4), array(
    'default'   => '0.0000',
), 'Cost')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
), 'Sort Order')
    ->addIndex('UNQ_POLICY_ID_SHIPPING_ID',
    array('policy_id', 'shipping_id'), array('unique' => true))
    ->addIndex('IDX_XCOM_MMP_CHANNEL_POLICY_SHIPPING_SHIPPING_ID',
    array('shipping_id'))
    ->addForeignKey('FK_XCOM_MMP_CHANNEL_POLICY_SHIPPING_SHIPPING_ID',
    'shipping_id', $this->getTable('xcom_mmp/shipping_service'), 'shipping_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey('FK_XCOM_MMP_CHANNEL_POLICY_SHIPPING_POLICY_ID',
    'policy_id', $this->getTable('xcom_mmp/policy'), 'policy_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Channel Policy Shipping');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'policy_shipping_id', 'int(10) unsigned NOT NULL auto_increment');

/**
 * Create table 'xcom_mmp/account'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mmp/account'))
    ->addColumn('account_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Account Id')
    ->addColumn('channeltype_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
    'nullable'  => false,
), 'Channeltype Code')
    ->addColumn('environment', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'nullable'  => false,
    'default'   => '0',
), 'Environment')
    ->addColumn('auth_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Auth Id')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'User Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'nullable'  => false,
), 'Created At')
    ->addColumn('validated_at', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
), 'Validated At')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'nullable'  => false,
    'default'   => '1',
), 'Status')
    ->addIndex('UNQ_ENVIRONMENT_USER_ID',
    array('environment', 'user_id'), array('unique' => true))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Account');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'account_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table 'xcom_mmp/country'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mmp/country'))
    ->addColumn('country_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Country Id')
    ->addColumn('channel_type_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Channel Type Code')
    ->addColumn('site_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Site Code')
    ->addColumn('environment', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Environment')
    ->addColumn('country_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 10, array(
), 'Country Code')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Description')
    ->addIndex('UNQ_CHANNEL_CODE_TYPE_ENV_HT',
    array('channel_type_code', 'site_code', 'environment', 'country_code'),
    array('unique' => true))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Core Country');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'country_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table 'xcom_mmp/currency'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mmp/currency'))
    ->addColumn('currency_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Currency Id')
    ->addColumn('channel_type_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Channel Type Code')
    ->addColumn('site_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Site Code')
    ->addColumn('currency', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Currency')
    ->addIndex('UNQ_CHANNEL_CODE_TYPE_ENV_CURRENCY',
    array('channel_type_code', 'site_code', 'currency'), array('unique' => true))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Core Currency');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'currency_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table 'xcom_mmp/environment'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mmp/environment'))
    ->addColumn('environment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Environment Id')
    ->addColumn('channel_type_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Channel Type Code')
    ->addColumn('site_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Site Code')
    ->addColumn('environment', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Environment')
    ->addIndex('UNQ_ENVIRONMENT_CODE_TYPE_ENV_ENVIRONMENT',
    array('channel_type_code', 'site_code', 'environment'), array('unique' => true))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Core Environment');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'environment_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table 'xcom_mmp/handling_time'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mmp/handling_time'))
    ->addColumn('handling_time_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Handling Time Id')
    ->addColumn('channel_type_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Channel Type Code')
    ->addColumn('site_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Site Code')
    ->addColumn('environment', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Environment')
    ->addColumn('max_handling_time', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
), 'Max Handling Time')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Description')
    ->addIndex('UNQ_CHANNEL_CODE_TYPE_ENV_HT',
    array('channel_type_code', 'site_code', 'environment', 'max_handling_time'), array('unique' => true))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Core Handling Time');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'handling_time_id', 'int(10) unsigned NOT NULL auto_increment');

/**
 * Create table 'xcom_mmp/payment_method'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mmp/payment_method'))
    ->addColumn('payment_method_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Payment Method Id')
    ->addColumn('channel_type_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Channel Type Code')
    ->addColumn('site_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Site Code')
    ->addColumn('environment', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Environment')
    ->addColumn('method_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Method Name')
    ->addIndex('UNQ_CHANNEL_CODE_TYPE_ENV_PM',
    array('channel_type_code', 'site_code', 'environment', 'method_name'), array('unique' => true))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Core Payment Method');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'payment_method_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table 'xcom_mmp/return_policy'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mmp/return_policy'))
    ->addColumn('return_policy_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Return Policy Id')
    ->addColumn('channel_type_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Channel Type Code')
    ->addColumn('site_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Site Code')
    ->addColumn('environment', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Environment')
    ->addColumn('returns_accepted', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'nullable'  => false,
    'default'   => '0',
), 'Returns Accepted')
    ->addColumn('max_return_by_days', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
), 'Max Return By Days')
    ->addColumn('methods', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Methods')
    ->addIndex('UNQ_CHANNEL_CODE_TYPE_ENV_RP',
    array('channel_type_code', 'site_code', 'environment'), array('unique' => true))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Core Return Policy');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'return_policy_id', 'int(10) unsigned NOT NULL auto_increment');


/**
 * Create table 'xcom_mmp/site'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_mmp/site'))
    ->addColumn('site_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Site Id')
    ->addColumn('channel_type_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Channel Type Code')
    ->addColumn('site_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Site Code')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Name')
    ->addIndex('UNQ_CHANNEL_TYPE_CODE_SITE_CODE',
    array('channel_type_code', 'site_code'), array('unique' => true))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Core Site');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'site_id', 'int(10) unsigned NOT NULL auto_increment');

$this->endSetup();
