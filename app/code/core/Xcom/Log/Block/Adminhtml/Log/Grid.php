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
 * @package     Xcom_Log
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Log_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('logGrid');
        $this->setDefaultSort('log_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare grid collection object
     *
     * @return Xcom_Log_Block_Adminhtml_Log_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('xcom_log/log')
            ->getCollection()
            ->addOrder('log_id', Varien_Db_Select::SQL_DESC);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return Xcom_Log_Block_Adminhtml_Log_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('description', array(
            'header'    => $this->__('Description'),
            'align'     =>'left',
            'index'     => 'description',
        ));

        $logResults = Mage::getSingleton('xcom_log/source_result')->toOptionHash();
        $this->addColumn('result', array(
            'header'    => $this->__('Result'),
            'align'     =>'left',
            'index'     => 'result',
            'type'      => 'options',
            'options'   => $logResults,
        ));

        $logTypes = Mage::getSingleton('xcom_log/source_type')->toOptionHash();
        $this->addColumn('type', array(
            'header'    => $this->__('Type'),
            'align'     =>'left',
            'index'     => 'type',
            'type'      => 'options',
            'options'   => $logTypes,
        ));

        $this->addColumn('created_at', array(
            'header'    => $this->__('Creation Date'),
            'align'     =>'left',
            'type'      =>'datetime',
            'index'     => 'created_at',
        ));

        return parent::_prepareColumns();
    }
}
