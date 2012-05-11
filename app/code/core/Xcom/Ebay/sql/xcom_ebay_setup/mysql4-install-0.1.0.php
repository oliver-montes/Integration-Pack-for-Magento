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
 * @package     Xcom_Ebay
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$this->startSetup();

/**
 * @var $this Mage_Core_Model_Resource_Setup
 * @var $table Varien_Db_Ddl_Table
 */

/**
 * Create table xcom_ebay_channel_policy
 */
$table = $this->getConnection()
    ->newTable($this->getTable('xcom_ebay/channel_policy'))
    ->addColumn('policy_ebay_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Policy Ebay Id')
    ->addColumn('policy_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned'  => true,
    'nullable'  => false,
), 'Policy Id')
    ->addColumn('payment_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Payment Name')
    ->addColumn('payment_paypal_email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Payment Paypal Email')
    ->addColumn('payment_paypal_immediate', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => '0',
), 'PayPal Immediate Payment')
    ->addColumn('location', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Location')
    ->addColumn('currency', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
    'nullable'  => false,
), 'Currency')
    ->addColumn('return_accepted', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => '0',
), 'Return Accepted')
    ->addColumn('return_by_days', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
), 'Return By Days')
    ->addColumn('refund_method', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Refund Method')
    ->addColumn('shipping_paid_by', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
), 'Shipping Paid By')
    ->addColumn('return_description', Varien_Db_Ddl_Table::TYPE_LONGVARCHAR, '64k', array(
), 'Return Description')
    ->addColumn('apply_tax', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'default'   => '0',
), 'Apply Tax')
    ->addColumn('handling_time', Varien_Db_Ddl_Table::TYPE_VARCHAR, 16, array(
), 'Handling Time')
    ->addColumn('postal_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 10, array(
), 'Postal Code')
    ->addIndex('UNQ_POLICY_EBAY_POLICY_ID',
    array('policy_id'), array('unique' => true))
    ->addForeignKey('FK_XCOM_EBAY_CHANNEL_POLICY_CHANNEL_POLICY',
    'policy_id', $this->getTable('xcom_mmp/policy'), 'policy_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setOption('collate', null)
    ->setOption('comment', 'Xcom Ebay Channel Policy');
$this->getConnection()->createTable($table);

$this->getConnection()->modifyColumn($table->getName(), 'policy_ebay_id',
    'int(10) unsigned NOT NULL auto_increment');

$this->endSetup();
