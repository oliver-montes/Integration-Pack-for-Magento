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

class Xcom_Mmp_Block_Adminhtml_Account_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set grid params
     */
    public function __construct()
    {
        parent::__construct();
        $this->setMassactionBlockName('xcom_mmp/adminhtml_widget_grid_massaction');
        $this->setDefaultSort('user_id');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection.
     *
     * @return Xcom_Mmp_Block_Adminhtml_Account_Grid
     */
    protected function _prepareCollection()
    {
        $channelType = Mage::registry('current_channeltype');
        /** @var $collection Xcom_Mmp_Model_Resource_Account_Collection */
        $collection = Mage::getModel('xcom_mmp/account')->getCollection()
            ->addChanneltypeCodeFilter($channelType->getCode())
            ->addValidationExpiredData();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return Xcom_Mmp_Block_Adminhtml_Account_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('user_id', array(
            'header'    => $this->__('User ID'),
            'index'     => 'user_id',
        ));

        $this->addColumn('environment', array(
            'header'    => $this->__('Environment'),
            'width'     => 90,
            'index'     => 'environment',
            'type'      => 'options',
            'options'   => $this->_getEnvironmentHash()
        ));

        $this->addColumn('validated_at', array(
            'header'    => $this->__('Validation Period'),
            'type'      => 'date',
            'renderer'  => 'xcom_mmp/adminhtml_widget_grid_column_renderer_validation_date',
            'index'     => 'validated_at',
            'width'     => 160,
            'default'   => ''
        ));

        $this->addColumn('status', array(
            'header'    => $this->__('Status'),
            'width'     => '120',
            'align'     => 'left',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                0 => $this->__('Disabled'),
                1 => $this->__('Enabled')
            ),
        ));

        $this->addColumn('action', array(
            'header'    => $this->__('Action'),
            'type'      => 'action',
            'width'     => '80px',
            'getter'    => 'getId',
            'filter'    => false,
            'sortable'  => false,
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return array
     */
    protected function _getEnvironmentHash()
    {
        return array();
    }

    /**
     * Prepare grid mass-action block
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('account_id');
        $this->getMassactionBlock()->setFormFieldName('account');

        $this->getMassactionBlock()->addItem('enable', array(
            'label'    => $this->__('Enable'),
            'url'      => $this->getUrl('*/*/massEnable'),
            'confirm'  => $this->__('Are you sure you want to enable these accounts?')
        ));
        $this->getMassactionBlock()->addItem('disable', array(
            'label'    => $this->__('Disable'),
            'url'      => $this->getUrl('*/*/massDisable'),
            'confirm'  => $this->__('Are you sure you want to disable these accounts?'),
            'validate_url' => $this->getUrl('*/*/massDisableValidation'),
        ));

        return $this;
    }
}
