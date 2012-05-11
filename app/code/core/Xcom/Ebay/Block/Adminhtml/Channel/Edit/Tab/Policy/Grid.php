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

class Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set grid params
     */
    public function __construct()
    {
        $this->setMassactionBlockName('xcom_mmp/adminhtml_widget_grid_massaction');

        parent::__construct();

        $this->setTemplate('xcom/ebay/channel/tab/policy/grid.phtml');
        $this->setId("policy_grid");
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setSortable(false);
    }

    /**
     * Prepare collection.
     *
     * @return Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('xcom_ebay/policy_collection')
            ->addOrder('status', 'ASC')
            ->addFieldToFilter('channel_id', Mage::registry('current_channel')->getId())
            ->addShippingMethods();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add eBay policy data to policy data collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->addPolicyData();
        return parent::_afterLoadCollection();
    }

    /**
     * Add columns to grid
     *
     * @return Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    => $this->__('Policy Name'),
            'index'     => 'name'
        ));
        $this->addColumn('payment_name', array(
            'header'    => $this->__('Payment'),
            'index'     => 'payment_name'
        ));
        $this->addColumn('shipping_description', array(
            'header'    => $this->__('Shipping Service'),
            'index'     => 'shipping_description'
        ));

        $this->addColumn('is_active', array(
            'header'    => $this->__('Status'),
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                1 => $this->__('Enabled'),
                0 => $this->__('Disabled')),
            'filter_index' => 'main_table.is_active',
        ));

        $this->addColumn('action', array(
            'header'    => $this->__('Action'),
            'type'      => 'action',
            'width'     => '80px',
            'getter'    => 'getId',
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'xcom_ebay/adminhtml_widget_grid_column_renderer_action',
            'actions'   => array( array(
                    'caption' => $this->__('Edit'),
                    'onclick' => array(
                        'method'    => 'loadPolicyContent',
                        'params'    => array(
                            'url'   => $this->getUrl('*/*/policy'),
                            'chanel_id'    => $this->getRequest()->getParam('channel_id'),
                        )
                    ),
                    'field'   => 'policy_id',
                ))
        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare grid mass-action block
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('policy_id');
        $this->getMassactionBlock()->setFormFieldName('selected_policy');
        $this->getMassactionBlock()->setUseAjax(true);

        $channelId = $this->getRequest()->getParam('channel_id');
        $this->getMassactionBlock()->addItem('enable', array(
            'label'=> $this->__('Enable'),
            'url'  => $this->getUrl('*/*/massEnablePolicy', array('channel_id'    => $channelId)),
            'confirm'  => $this->__('Are you sure you want to enable policy(s)?')
        ));
        $this->getMassactionBlock()->addItem('disable', array(
             'label'=> $this->__('Disable'),
             'url'  => $this->getUrl('*/*/massDisablePolicy', array('channel_id'    => $channelId)),
             'confirm' => $this->__('Are you sure you want to disable policy(s)?')
        ));

        return parent::_prepareMassaction();
    }

    /**
     * If Policy failed, then return CSS class to show table row disabled.
     *
     * @param Varien_Object $row
     * @return bool|string
     */
    public function getRowClass(Varien_Object $row) {
        if ($this->isDisabled($row)) {
            return 'policy-disabled';
        }
        return false;
    }

    /**
     * Check if Policy failed.
     *
     * @param Varien_Object $row
     * @return bool
     */
    public function isDisabled(Varien_Object $row) {
        if ($row->getStatus() == Xcom_Mmp_Model_Policy::XML_POLICY_STATUS_FAILED) {
            return true;
        }
        return false;
    }

    /**
     * @param Varien_Object $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Grid
     */
    public function setColumnDisabled(Varien_Object $row, Mage_Adminhtml_Block_Widget_Grid_Column $column)
    {
        $column->setDisabledValue($row->getData($column->getIndex()));
        $this->resetMassactionRenderer($column);
        return $this;
    }

    /**
     * Reset renderer for massaction type column.
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Grid
     */
    public function resetMassactionRenderer(Mage_Adminhtml_Block_Widget_Grid_Column $column)
    {
        if ($column->getData('type') == 'massaction') {
            $column
                ->setData('type')   // Unset massaction column type
                ->setData('renderer', 'xcom_ebay/adminhtml_widget_grid_column_renderer_empty')
                ->setRenderer(null) // Unset massaction renderer
                ->getRenderer();    // Add new renderer to column
        }
        return $this;
    }
}
