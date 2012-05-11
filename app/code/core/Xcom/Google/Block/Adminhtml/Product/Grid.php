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
 * @package     Xcom_Google
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Google_Block_Adminhtml_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Array of channels.
     *
     * @var array
     */
    protected $_channelOptions;

    /**
     * Channel simple options array list
     *
     * @var array
     */
    protected $_channelOptionHash;

    /**
     * Channel collection model
     *
     * @var Xcom_Cse_Model_Resource_Channel_Collection
     */
    protected $_channelCollection;

    /**
     * Statuses list
     *
     * @var array
     */
    protected $_statuses = array(
        array(
            'option_id' => Xcom_Listing_Model_Channel_Product::STATUS_ACTIVE,
            'caption'   => 'Active',
            'type'      => 'link'),
        array(
            'option_id' => Xcom_Listing_Model_Channel_Product::STATUS_INACTIVE,
            'caption'   => 'Inactive',
            'type'      => 'link'),
        array(
            'option_id' => Xcom_Listing_Model_Channel_Product::STATUS_PENDING,
            'caption'   => 'Pending',
            'type'      => 'link'),
        array(
            'option_id' => Xcom_Listing_Model_Channel_Product::STATUS_FAILURE,
            'caption'   => 'Failure',
            'type'      => 'link')
    );

    /**
     * Initialize class.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('channelProductGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Retrieve currently edited channeltype object.
     *
     * @return Varien_Object
     */
    public function getChanneltype()
    {
        return Mage::registry('current_channeltype');
    }

    /**
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    /**
     * Customize add filters to collection.
     *
     * @param $column
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if (!in_array($column->getId(), array('maxtimestamp', 'channels', 'cseoffer'))) {
                return parent::_addColumnFilterToCollection($column);
            }
            $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
            $condition = $column->getFilter()->getCondition();

            if ($field && isset($condition)) {
                switch ($column->getId()) {
                    case 'maxtimestamp':
                        $this->getCollection()->prepareMaxTimestampFilter($condition);
                        break;
                    case 'channels':
                        $this->getCollection()->prepareChannelsFilter($condition);
                        break;
                    case 'cseoffer':
                        $this->getCollection()->prepareCseOffersFilter($condition);
                        break;
                }
            }
        }
        return $this;
    }

    /**
     * Prepare grid collection object
     *
     * @return Xcom_Google_Block_Adminhtml_Product_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Xcom_CseOffer_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('xcom_cseoffer/product_collection')
            ->joinProductQty()
            ->prepareStoreSensitiveData($this->_getStore())
            ->addFieldToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->joinStatusColumn($this->getChanneltype()->getCode());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Return product status action for grid column
     *
     * @param  $storeId
     * @return array
     */
    protected function _prepareStatusLinkActions($storeId)
    {
        $actions = array();
        foreach ($this->_statuses as $status) {
            $actions[] = array(
                'option_id' => $status['option_id'],
                'caption'   => $this->__($status['caption']),
                'type'      => $status['type'],
                'field'     => array(
                    'id'        => 'entity_id',
                    'channel'   => 'channels'
                    ),
                'url'       => array(
                    'base'      => '*/google_product/history',
                    'params'    => array(
                        'store' => $storeId),
                    ),
            );
        }
        return $actions;
    }

    /**
     * Prepare grid columns.
     *
     * @return Xcom_Google_Block_Adminhtml_Product_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id',
            array(
                'header' => $this->__('ID'),
                'width'  => '50px',
                'type'   => 'number',
                'index'  => 'entity_id',
        ));

        $this->addColumn('name',
            array(
                'header' => $this->__('Name'),
                'index'  => 'name',
        ));

        // only show simple products
        $productTypes = Mage::getSingleton('catalog/product_type')->getOptionArray();
        $this->addColumn('type',
            array(
                'header'=> $this->__('Type'),
                'width' => '120px',
                'index' => 'type_id',
                'type'  => 'options',
                'filter'  => 'xcom_google/adminhtml_renderer_grid_filter_select',
                'options' => array('simple' => $productTypes['simple']),
        ));

        $this->addColumn('set_name',
            array(
                'header'  => $this->__('Attrib. Set Name'),
                'width'   => '100px',
                'index'   => 'attribute_set_id',
                'type'    => 'options',
                'options' => $this->_getAttributeSetOptionHash(),
        ));

        $this->addColumn('sku',
            array(
                'header' => $this->__('SKU'),
                'width'  => '80px',
                'index'  => 'sku',
        ));

        $this->addColumn('price',
            array(
                'header'        => $this->__('Price'),
                'type'          => 'price',
                'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
                'index'         => 'price',
        ));

        $this->addColumn('qty',
            array(
                'header' => $this->__('Magento Qty'),
                'width'  => '100px',
                'type'   => 'number',
                'index'  => 'qty',
        ));

        $this->addColumn('channels',
            array(
                'header'    => $this->__('Channels Published To'),
                'type'      => 'options',
                'name'      => 'channels',
                'options'   => $this->getChannelOptionHash(),
                'index'     => 'channels',
                'sortable'  => false,
                'renderer'  => 'xcom_cse/adminhtml_widget_grid_column_renderer_multiple_options'
        ));

        $this->addColumn('maxtimestamp',
            array(
                'header'  => $this->__('Timestamp'),
                'type'    => 'datetime',
                'index'   => 'maxtimestamp',
                'name'    => 'maxtimestamp',
        ));

        $storeId = $this->getRequest()->getParam('store');
        $this->addColumn('cseoffer', array(
            'header'    => $this->__('Channel Listing Status'),
            'width'     => 90,
            'type'      => 'options',
            'options'   => Mage::getSingleton('xcom_cseoffer/source_offer_status')->toOptionHash(),
            'sortable'  => false,
            'index'     => 'cseoffer',
            'renderer'  => 'xcom_cse/adminhtml_widget_grid_column_renderer_multiple_actions',
            'actions'   => $this->_prepareStatusLinkActions($storeId),
        ));

        return parent::_prepareColumns();
    }

    /**
     * Get attribute set options hash
     *
     * @return array
     */
    protected function _getAttributeSetOptionHash()
    {
        return Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();
    }

    /**
     * Prepare grid mass-action block
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('selected_products');
        $channels = $this->getChannelOptionArray();

        $choosenChannel = count($channels) === 1 ? $channels[0]['value'] : '';

        array_unshift($channels, array('label' => $this->__('--- Choose a Channel ---'), 'value' => ''));

        $additional = array(
            'channel_id' => array(
                'name' => 'channel_id',
                'type' => 'select',
                'class' => 'required-entry',
                'label' => $this->__('Channel'),
                'values' => $channels,
                'value' => $choosenChannel
            )
        );
        $this->getMassactionBlock()->addItem('create', array(
             'label'=> $this->__('Publish to Channel'),
             'url'  => $this->getUrl('*/google_product/publish', array('_current' => false)),
             'additional' => $additional,
        ));
        $this->getMassactionBlock()->addItem('cancel', array(
             'label'=> $this->__('Remove from Channel'),
             'url'  => $this->getUrl('*/google_product/massCancel'),
             'confirm' => $this->__('Your offers in the selected channel(s) will be removed.'),
             'additional' => $additional,
        ));

        return parent::_prepareMassaction();
    }

    /**
     * Grid url getter
     *
     * @return string current grid url
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * Retrieve array of channels for current channel type.
     *
     * @return array
     */
    public function getChannelOptionArray()
    {
        if (null === $this->_channelOptions) {

            $this->_channelOptions = $this->_initChannelCollection()
                ->addChanneltypeCodeFilter($this->getChanneltype()->getCode())
                ->toOptionArray();
            foreach ($this->_channelOptions as &$channel) {
                $channel['label'] = $this->escapeHtml($channel['label']);
            }
        }
        return $this->_channelOptions;
    }

    /**
     * Get channel options hash
     *
     * @return array
     */
    public function getChannelOptionHash()
    {
        if (null === $this->_channelOptionHash) {
            $this->_channelOptionHash = $this->_initChannelCollection()
                ->toOptionHash();
            foreach ($this->_channelOptionHash as &$label) {
                $label = $this->escapeHtml($label);
            }
        }
        return $this->_channelOptionHash;
    }

    /**
     * Init channel collection
     *
     * @return Xcom_Cse_Model_Resource_Channel_Collection
     */
    protected function _initChannelCollection()
    {
        if (null === $this->_channelCollection) {
            $this->_channelCollection = Mage::getResourceModel('xcom_cse/channel_collection');
            $this->_channelCollection->addChanneltypeCodeFilter($this->getChanneltype()->getCode());
        }
        return $this->_channelCollection;
    }
}
