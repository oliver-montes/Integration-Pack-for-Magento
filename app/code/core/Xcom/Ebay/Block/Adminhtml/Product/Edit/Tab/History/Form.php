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
class Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_History_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init Form properties
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('xcom_ebay_product_tab_history_form');
        $this->setTemplate('xcom/ebay/product/tab/form.phtml');
    }

    /**
     * Prepare product collection
     *
     * @return Xcom_Listing_Model_Resource_Product_Collection
     */
    protected function _prepareCollection()
    {
        $productId  = $this->getRequest()->getParam('id');
        $channelId = $this->getRequest()->getParam('channel');
        $storeId    = $this->getRequest()->getParam('store');
        if (empty($storeId)) {
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        }
        $collection = Mage::getResourceModel('xcom_listing/product_collection');
        $collection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner', $storeId)
            ->joinChannelData($channelId)
            ->addFieldToFilter('entity_id', array('eq' => $productId))
            ->joinStatusColumn($this->_getChannel()->getChanneltypeCode());
        return $collection;
    }

    /**
     * @return mixed
     */
    protected function _getChannel()
    {
        return Mage::registry('current_channel');
    }

    /**
     * Prepare form fields
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $collection = $this->_prepareCollection();
        /** @var $product Xcom_Listing_Model_Channel_Product */
        $product    = $collection->getLastItem();
        $channelId = $this->getRequest()->getParam('channel');
        $form = new Varien_Data_Form(array(
            'id'        => $this->getId(),
            'method' => 'post'
        ));
        $fieldset = $form->addFieldset('base_fieldset', array());
        $fieldset->addField('sku', 'note', array(
            'name'      => 'sku',
            'label'     => $this->__('SKU'),
            'title'     => $this->__('SKU'),
            'text'     => $this->_getProductLink($product)
        ));

        $fieldset->addField('name', 'label', array(
            'name'      => 'name',
            'label'     => $this->__('Name'),
            'title'     => $this->__('Name'),
            'value'     => $product->getName()
        ));


        $channel = Mage::getModel('xcom_ebay/channel')->load((int)$channelId);
        $fieldset->addField('channel_name', 'select', array(
            'name'      => 'channel_name',
            'label'     => $this->__('Channel'),
            'title'     => $this->__('Channel'),
            'values'    => $this->_getChannelsArray(),
            'value'     => $channel->getId(),
            'onchange'   => 'return switchChannel(this);'
        ));

        $fieldset->addField('channel_listing_status', 'label', array(
            'name'      => 'channel_listing_status',
            'label'     => $this->__('Channel Listing Status'),
            'title'     => $this->__('Channel Listing Status'),
            'value'     => $product->getChannelListingStatus()

        ));

        $fieldset->addField('channel_listing_view', (strlen($product->getLink()) > 0) ? 'link' : 'label', array(
            'name'      => 'channel_listing_view',
            'label'     => $this->__('View Listing'),
            'title'     => $this->__('View Listing'),
            'href'      => $product->getLink(),
            'target'    => '_blank',
            'value'     => $this->__((strlen($product->getLink()) > 0) ? $this->__('Link to Channel Listing') :
                $this->__('Not available'))
        ));
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Return product url
     *
     * @param $product Varien_Object
     * @return string
     */
    protected function _getProductLink($product)
    {
        $sku = '<a href="' . $this->getUrl('*/catalog_product/edit', array('id' => $product->getId())) . '" ' .
        'target="_blank" >'.
        htmlspecialchars($product->getSku()) . '</a>';
        return $sku;
    }

    /**
     * Return channels
     *
     * @return array
     */
    protected function _getChannelsArray()
    {
        /** @var $channelProduct Xcom_Listing_Model_Resource_Channel_Product_Collection */
        $channelProduct = Mage::getResourceModel('xcom_listing/channel_product_collection')
            ->addChannelProduct((int)$this->getRequest()->getParam('id'));
        return $channelProduct->toOptionHash();
    }

    /**
     * Return URL, need for switching between channels
     *
     * @return string
     */
    public function getSwitchUrl()
    {
        return $this->getUrl('*/*/history', array(
            'id'    => (int) $this->getRequest()->getParam('id'),
            'store' => (int) $this->getRequest()->getParam('store'),
        ));
    }
}
