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
class Xcom_Google_Block_Adminhtml_Product_Edit_Tab_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize class.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('google_channel_product_history_grid');
        $this->setSaveParametersInSession(true);
        $this->setEmptyText($this->__('These products have not been published to any of Google Channels yet.'));
        $this->setDefaultSort('created_at');
    }

    /**
     * Retrieve current channel object.
     *
     * @return Xcom_Google_Model_Channel
     */
    public function getCurrentChannel()
    {
        return Mage::registry('current_channel');
    }

    /**
     * Prepare collection.
     *
     * @return Xcom_Listing_Model_Resource_Channel_History_Collection
     */
    protected function _prepareCollection()
    {
        $channelId = $this->getRequest()->getParam('channel');
        $product_id = $this->getRequest()->getParam('id');
        /** @var $collection Xcom_Listing_Model_Resource_Channel_History_Collection */
        $collection = Mage::getModel('xcom_listing/channel_history')->getCollection()
            ->addFieldToFilter('product_id', (int)$product_id)
            ->addChanneltypeFilter($this->getCurrentChannel()->getChanneltypeCode())
            ->addChannelFilter($channelId);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('created_at', array(
            'header'    => $this->__('Publish Date'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'default'   => '--',
            'width'     => 160,
        ));

        $this->addColumn('action', array(
            'header'    => $this->__('Action'),
            'align'     => 'left',
            'type'      => 'options',
            'options'   => array('create' => 'Create', 'update' => 'Update', 'remove' => 'Remove'),
            'index'     => 'action',
        ));

        // Result
        $this->addColumn('price', array(
            'header'        => $this->__('Price'),
            'type'          => 'price',
            'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
            'index'         => 'price',
        ));

        $this->addColumn('qty', array(
            'header' => $this->__('Qty Published'),
            'type'   => 'number',
            'index'  => 'qty',
        ));

        $this->addColumn('response_result', array(
            'header'        => $this->__('Result'),
            'type'      => 'options',
            'options'   => array(
                Xcom_Listing_Model_Channel_Product::STATUS_PENDING => 'Pending',
                Xcom_Listing_Model_Channel_Product::STATUS_ACTIVE  => 'Success',
                Xcom_Listing_Model_Channel_Product::STATUS_FAILURE => 'Error'
            ),
            'index'     => 'response_result',
        ));

        $this->addColumn('category', array(
            'header'    => $this->__('Category'),
            'type'      => 'text',
            'index'     => 'category'
        ));

        $this->addColumn('policy', array(
            'header'    => $this->__('Policy'),
            'type'      => 'text',
            'index'     => 'policy',
            'renderer'  => 'adminhtml/widget_grid_column_renderer_text'
        ));

        $this->addColumn('listinglog', array(
            'header'        => $this->__('Log Entry'),
            'type'      => 'action',
            'filter'    => false,
            'getter' => 'getLogResponseId',
            'renderer'  => 'xcom_ebay/adminhtml_widget_grid_column_renderer_multiple_actions',
            'actions'   => array(array(
                'caption' => $this->__('View'),
                'url'     => array(
                    'base'   => '*/*/listingerror',
                    'params' => array(
                        'id' => $this->getRequest()->getParam('id'),
                        'channel' => $this->getRequest()->getParam('channel')
                    ),
                ),
                'field'   => 'response',
            )),
        ));

        return parent::_prepareColumns();
    }
}
