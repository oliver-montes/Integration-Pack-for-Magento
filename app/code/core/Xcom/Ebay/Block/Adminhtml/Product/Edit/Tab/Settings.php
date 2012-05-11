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

class Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Listing model
     *
     * @var Xcom_Listing_Model_Listing
     */
    protected $_listing;

    /**
     * Is each product in channel?
     *
     * @var bool
     */
    protected $_isEachProductInChannel;

    /**
     * Policy options hash
     *
     * @var array
     */
    protected $_policyOptionHash;

    /**
     * Policy options
     *
     * @var array
     */
    protected $_policyOptionArray;

    /**
     * Constructor
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('publish_settings_form');
        $this->setTemplate('xcom/ebay/product/settings-form.phtml');
    }

    /**
     * Prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    public function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('base_fieldset', array());

        $fieldset->addType('publish_price',
            Mage::getConfig()->getBlockClassName('xcom_ebay/adminhtml_renderer_publish_price'));
        $fieldset->addType('publish_quantity',
            Mage::getConfig()->getBlockClassName('xcom_ebay/adminhtml_renderer_publish_quantity'));
        $fieldset->addType('publish_categories',
            Mage::getConfig()->getBlockClassName('xcom_ebay/adminhtml_renderer_publish_categories'));

        $fieldset->addField('product_ids', 'hidden', array(
            'name' => 'product_ids',
            'value' => implode(',', $this->getProductIds())
        ));

        $channel = $this->getCurrentChannel();

        $fieldset->addField('publish_channel', 'label', array(
            'name'      => 'publish_channel',
            'label'     => $this->__('Channel'),
            'value'     => $this->escapeHtml($channel->getName()),
            'value_class'   => 'channel-products-value',
        ));

        $fieldset->addField('channel_id', 'hidden', array(
            'name' => 'channel_id',
            'value' => $channel->getId(),
        ));


        /** @var $block Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_CategoriesTree */
        $block = $this->getChild('ebay.edit.product.categories');
        $block->addData(array(
            'selected_category_id'       => $this->getListing()->getCategoryId(),
            'required'                   => !$this->_isEachProductInChannel(),
        ));

        $fieldset->addField('category', 'publish_categories', array(
            'name'        => 'category',
            'label'       => $this->__('Category'),
            'required'    => !$this->_isEachProductInChannel(),
            'value'       => array('block_categories' => $block)
        ));

        $priceValue = $this->getListing()->getPriceValue();
        $priceValue = null === $priceValue ? $priceValue : $priceValue * 1;

        $fieldset->addField('price', 'publish_price', array(
            'name'          => 'price',
            'label'         => $this->__('Price'),
            'required'      => !$this->_isEachProductInChannel(),
            'value'         => array(
                'value'       => $priceValue,
                'type'        => $this->getListing()->getPriceType(),
                'value_type'  => $this->getListing()->getPriceValueType()
            ),
        ));

        $qtyValue = is_null($this->getListing()->getQtyValue()) ? $this->getListing()->getQtyValue() :
            $this->getListing()->getQtyValue() * 1;

        $fieldset->addField('qty', 'publish_quantity', array(
            'name'          => 'qty',
            'label'         => $this->__('Quantity'),
            'required'      => !$this->_isEachProductInChannel(),
            'value_class'   => 'channel-products-value',
            'value'         => array(
                'value'         => $qtyValue,
                'value_type'    => $this->getListing()->getQtyValueType()
            ),
        ));

        $triggerUpdateJsText = '<script type="text/javascript">triggerUpdateState = true;</script>';
        $fieldset->addField('policy_id', 'select', array(
            'required'  => !$this->_isEachProductInChannel(),
            'label'     => $this->__('Policy'),
            'values'    => $this->getPolicyOptionArray(),
            'class'     => 'select',
            'name'      => 'policy_id',
            'value_class'   => 'channel-products-value',
            'value'     => $this->getListing()->getPolicyId(),
            'after_element_html' => $this->_isEachProductInChannel() ? $triggerUpdateJsText : '',
        ));

        return parent::_prepareForm();
    }

    /**
     * Get listing model
     *
     * @return Xcom_Listing_Model_Listing
     */
    public function getListing()
    {
        if (!is_object($this->_listing)) {
            $listingId = $this->_getPublishedListingId();
            $this->_listing = Mage::getModel('xcom_listing/listing');
            if ($listingId) {
                $this->_listing->load($listingId);
            }
        }
        return $this->_listing;
    }

    /**
     * Get published listing ID
     *
     * @return int
     */
    protected function _getPublishedListingId()
    {
        $productIds = $this->getProductIds();
        $channelId = $this->getCurrentChannel()->getId();
        $listingId = Mage::getSingleton('xcom_listing/channel_product')
            ->getPublishedListingId($channelId, $productIds);
        return (int)$listingId;
    }

    /**
     * Retrieve current channel object.
     *
     * @return Xcom_Ebay_Model_Channel
     */
    public function getCurrentChannel()
    {
        return Mage::registry('current_channel');
    }

    /**
     * Get product ids from request.
     *
     * @return array
     */
    public function getProductIds()
    {
        return $this->helper('xcom_ebay')->getRequestProductIds();
    }

    /**
     * Is each product in channel
     *
     * @return bool
     */
    protected function _isEachProductInChannel()
    {
        if ($this->_isEachProductInChannel === null) {
            $this->_isEachProductInChannel = Mage::getSingleton('xcom_listing/channel_product')
                ->isProductsInChannel($this->getCurrentChannel()->getId(), $this->getProductIds());
        }

        return $this->_isEachProductInChannel;
    }

    /**
     * Retrieve policy list for channel
     *
     * @return array
     */
    public function getPolicyOptionArray()
    {

        if (null === $this->_policyOptionArray) {
            $this->_policyOptionArray = Mage::getModel('xcom_ebay/policy')->getCollection()
                ->addFieldToFilter('channel_id', $this->getCurrentChannel()->getId())
                ->addFieldToFilter('is_active', '1')
                ->addFieldToFilter('xprofile_id', array('notnull' => true))
                ->toOptionArray();

            if (!$this->getListing()->getPolicyId() && !$this->_isEachProductInChannel() &&
                count($this->_policyOptionArray) === 1) {
                $this->getListing()->setPolicyId($this->_policyOptionArray[0]['value']);
            }

            array_unshift($this->_policyOptionArray, array('value' => '', 'label' => $this->__('Please Select One')));
        }
        return $this->_policyOptionArray;
    }

    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Publish to Channel');
    }

    /**
     * Get tab label title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Publish to Channel');
    }

    /**
     * Is can show tab?
     *
     * @return string
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Is tab hidden?
     *
     * @return string
     */
    public function isHidden()
    {
        return false;
    }
}
