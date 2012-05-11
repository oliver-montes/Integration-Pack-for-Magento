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
 * @package     Xcom_Listing
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Listing_Model_Resource_Category_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    const XCOM_CACHE_TAG    = 'XCOM_CATEGORY_COLLECTION';
    /**
     * Tree in for toOptionArray() method
     *
     * @var array
     */
    protected $_optionGroupItems = array();

    protected $_recommendedCategoryIds = array();

    protected $_attributeSetId = 0;

    /**
     * Has to be an array and have strict structure
     * array(
     *      'siteCode'  => ...,
     *      'environmentName' => ...,
     * )
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Initialize resource model for collection
     *
     */
    protected function _construct()
    {
        $this->_init('xcom_listing/category');
        $this->initCache(Mage::app()->getCache(), 'xcom', array(self::XCOM_CACHE_TAG));
    }

    /**
     * Adds filter py attribute set
     *
     * @param int $attributeSetId
     * @return Xcom_Listing_Model_Resource_Category_Collection
     */
    public function addAttributeSetFilter($attributeSetId)
    {
        $this->getSelect()
            ->joinLeft(array('cpt' => $this->getTable('xcom_listing/category_product_type')),
                'main_table.category_id = cpt.category_id',
                array())
            ->joinLeft(array('mptr' => $this->getTable('xcom_mapping/product_type_relation')),
                'cpt.mapping_product_type_id = mptr.mapping_product_type_id AND '.
                $this->getConnection()->quoteInto('mptr.attribute_set_id = ?', (int)$attributeSetId),
                array('attribute_set_id' => 'MAX(mptr.attribute_set_id)'))
            ->group('main_table.id');

        $this->_attributeSetId = (int)$attributeSetId;
        return $this;
    }

    /**
     * Returns Ids od recommended categories
     *
     * @return array
     */
    public function getRecommendedIds()
    {
        $this->_getOptionGroupItems();
        return $this->_recommendedCategoryIds;
    }

    /**
     * Sets options
     *
     * @param array $options
     * @return Xcom_Listing_Model_Resource_Category_Collection
     */
    public function setOptions($options)
    {
        $this->_options = $options;
        $this->getSelect()
            ->where('main_table.marketplace = ?', $options['channeltypeCode'])
            ->where('main_table.environment_name = ?', $options['environmentName'])
            ->where('main_table.site_code = ?', $options['siteCode']);

        return $this;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = array();
        foreach ($this->_getOptionGroupItems() as $item) {
            $option = $this->_prepareOption($item->getName(), array());
            $this->_addValueToOption($option, $item);

            if (empty($option['value'])) {
                $option['value'] = $item->getData('id');
            }

            // Skip top categories without children if they are not recommended for set
            if ($this->_attributeSetId > (int)$item->getAttributeSetId() && !is_array($option['value'])) {
                continue;
            }
            $optionArray[] = $option;
        }

        return $optionArray;
    }

    /**
     * Generates parent-child links for items
     *
     * @return array
     */
    protected function _getOptionGroupItems()
    {
        if (!empty($this->_optionGroupItems)) {
            return $this->_optionGroupItems;
        }

        //collect possible parentIds
        $possibleParents = array();
        foreach ($this as $item) {
            $item->setChildren(array());
            $possibleParents[(int)$item->getData('id')] = $item;
        }

        //link Items;
        foreach ($this as $item) {
            $this->_checkAndCollectRecommendedCategory($item);
            //link to parent;
            $parentId = (int)$item->getParentId();
            if (isset($possibleParents[$parentId]) && $parentId != (int)$item->getData('id')) {
                $parentItem = $possibleParents[$parentId];
                $item->setParent($parentItem);
                $parentItem->setChildren(array_merge($parentItem->getChildren(), array($item)));
            } else {
                // Add as root
                $this->_optionGroupItems[(int)$item->getData('id')] = $item;
            }
        }

        return $this->_optionGroupItems;
    }

    /**
     * @param Varien_Object $item
     * @return Xcom_Listing_Model_Resource_Category_Collection
     */
    protected function _checkAndCollectRecommendedCategory(Varien_Object $item)
    {
        if ($this->_isRecommendedCategory($item)) {
            $this->_recommendedCategoryIds[] = (int)$item->getData('id');
        }
        return $this;
    }

    /**
     * @param Varien_Object $item
     * @return bool
     */
    protected function _isRecommendedCategory(Varien_Object $item)
    {
        return $this->_attributeSetId && $this->_attributeSetId == (int)$item->getAttributeSetId();
    }

    /**
     * Fills up parent values
     *
     * @param array $options
     * @param Varien_Object $element
     * @return Xcom_Listing_Model_Resource_Category_Collection
     */
    protected function _addValueToOption(array &$options, Varien_Object $element)
    {
        foreach ($element->getChildren() as $child) {
            if ($child->getLeafCategory()) {
                $options['value'][] = $this->_prepareOption($child->getName(), $child->getData('id'));
            }
            $this->_addValueToOption($options, $child);
        }
        return $this;
    }

    /**
     * @param string $label
     * @param mixed $value
     * @return array
     */
    protected function _prepareOption($label, $value)
    {
        return array(
            'label' =>  Mage::helper('core')->stripTags($label),
            'value' => $value,
        );
    }

    /**
     * Overwrite parent method, do not cache collection because we already cache select field
     * in Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_Categories class
     *
     * @return bool
     */
    protected function _canUseCache()
    {
        return false;
    }

    /**
     * Reset collection state
     *
     * @return Xcom_Listing_Model_Resource_Category_Collection
     */
    public function reset()
    {
        $this->_totalRecords        = null;
        $this->_data                = null;
        $this->_items               = array();
        $this->_isCollectionLoaded  = null;
        return $this;
    }
}
