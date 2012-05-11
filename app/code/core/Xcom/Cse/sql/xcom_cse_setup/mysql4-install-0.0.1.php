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
 * @package     Xcom_Cse
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$this->startSetup();

/**
 * @var $this Mage_Core_Model_Resource_Setup
 * @var $table Varien_Db_Ddl_Table
 */

/**
 * Create table 'xcom_cse/channel'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_cse/channel'))
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
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
    'default'   => '',
), 'Name')
    ->addColumn('offer_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
    'default'   => '',
), 'Offer Name')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Store Id')
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
    ->addForeignKey('FK_XCOM_CSE_CHANNEL_ACCOUNT_ID',
    'account_id', $this->getTable('xcom_cse/account'), 'account_id',
    Varien_Db_Ddl_Table::ACTION_NO_ACTION, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->addForeignKey('FK_XCOM_CSE_CHANNEL_STORE_ID',
    'store_id', $this->getTable('core/store'), 'store_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Channel');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'channel_id', 'int(10) unsigned NOT NULL auto_increment');

/**
 * Create table 'xcom_cse/account'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_cse/account'))
    ->addColumn('account_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Account Id')
    ->addColumn('channeltype_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
    'nullable'  => false,
), 'Channeltype Code')
    ->addColumn('auth_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => true,
), 'Auth Id')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'User Id')
    ->addColumn('target_location', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => true,
), 'Target Location')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'nullable'  => false,
), 'Created At')
    ->addColumn('validated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'nullable'  => true,
), 'Validated At')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'nullable'  => false,
    'default'   => '1',
), 'Status')
    ->addIndex('UNQ_AUTH_ID_USER_ID_TARGET',
    array('auth_id', 'user_id', 'target_location'), array('unique' => true))
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Account');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'account_id', 'int(10) unsigned NOT NULL auto_increment');

/**
 * Create table 'xcom_cse/site'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_cse/site'))
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

/*
 * Populate the site table for this release.
 */
$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
$sql = "INSERT INTO `xcom_cse_site` (`site_id`,`channel_type_code`,`site_code`,`name`) VALUES (NULL,'google','US','United States')";
$connection->query($sql);
$sql = "INSERT INTO `xcom_cse_site` (`site_id`,`channel_type_code`,`site_code`,`name`) VALUES (NULL,'google','AU','Australia')";
$connection->query($sql);
$sql = "INSERT INTO `xcom_cse_site` (`site_id`,`channel_type_code`,`site_code`,`name`) VALUES (NULL,'google','FR','France')";
$connection->query($sql);
$sql = "INSERT INTO `xcom_cse_site` (`site_id`,`channel_type_code`,`site_code`,`name`) VALUES (NULL,'google','DE','Germany')";
$connection->query($sql);
$sql = "INSERT INTO `xcom_cse_site` (`site_id`,`channel_type_code`,`site_code`,`name`) VALUES (NULL,'google','UK','United Kingdom')";
$connection->query($sql);

$this->endSetup();