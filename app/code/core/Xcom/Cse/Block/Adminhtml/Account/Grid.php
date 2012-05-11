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

class Xcom_Cse_Block_Adminhtml_Account_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set grid params
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('user_id');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection.
     *
     * @return Xcom_Cse_Block_Adminhtml_Account_Grid
     */
    protected function _prepareCollection()
    {
        $channelType = Mage::registry('current_channeltype');
        /** @var $collection Xcom_Cse_Model_Resource_Account_Collection */
        $collection = Mage::getModel('xcom_cse/account')->getCollection()
            ->addChanneltypeCodeFilter($channelType->getCode());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return Xcom_Cse_Block_Adminhtml_Account_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('user_id', array(
            'header'    => $this->__('Account ID'),
        	'width'		=> '120px',
            'index'     => 'user_id',
        ));
        
        $this->addColumn('target_location', array(
            'header'    => $this->__('Google Storage bucket name'),
        	'width'		=> '120px',
            'index'     => 'target_location',
        ));

        $this->addColumn('status', array(
            'header'    => $this->__('Status'),
            'width'     => '120px',
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
}
