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

/**
 * Categories tree block
 *
 * @TODO Move this class to Xcom_Listing module and make like abstract because categories is abstract entity
 */
class Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_CategoriesTree extends Mage_Adminhtml_Block_Widget
{
    /**
     * Channel model
     *
     * @var Xcom_Ebay_Model_Channel
     */
    protected $_channel;

    /**
     * Recommended categories pairs ID => path
     *
     * @var array
     */
    protected $_recommendedCategories;

    /**
     * Collection array
     *
     * @var Xcom_Listing_Model_Resource_Category_Collection
     */
    protected $_categoryCollection;

    /**
     * Parent ID of category
     *
     * @var int
     */
    protected $_parentId;

    /**
     * return selected category form data with 'selected_category_id'
     * if there on any and recommended category only one
     * than return it as selected
     *
     * @return int
     */
    public function getSelectedCategory()
    {
        $recommendedCategories = $this->_getRecommendedCategories();
        if (count($recommendedCategories) == 1
            && !$this->getData('selected_category_id')
        ) {
            $arr = array_keys($recommendedCategories);
            $selectedCategoryId = array_shift($arr);
            $this->setData('selected_category_id', $selectedCategoryId);
        }
        return $this->getData('selected_category_id');
    }
    /**
     * Return options for form
     *
     * @param bool $recommended
     * @return string
     */
    public function getTreeJson($recommended = false)
    {
        $collection = $this->_getCategoryCollection();
        $collection->getSelect()->reset(Varien_Db_Select::WHERE);
        $collection->getSelect()->reset(Varien_Db_Select::ORDER);
        $collection->reset();
        $this->_setDefaultCollectionFilters();

        $level = null;

        $selectedCategoryId = $this->getSelectedCategory();

        if ($recommended) {
            $recommendedCategories = $this->_getRecommendedCategories();
            if (!$recommendedCategories) {
                //return empty JSON array
                return '[]';
            }
            $ids = array();
            foreach ($recommendedCategories as $path) {
                $arr = explode('/', $path);
                $ids = array_merge($ids, $arr);
            }
            $ids = array_unique($ids);

            $collection->addFilter('id', array('in' => $ids), 'public');

        } else {
            if (!$this->_parentId) {
                $level = 1; //load only root items
                $collection->addFieldToFilter('level', $level);
            } else {
                $collection->addFieldToFilter('main_table.parent_id', $this->_parentId)
                    ->addFilter('main_table.id', array('neq' => $this->_parentId), 'public');
            }
        }

        $collection->setOrder('name', Varien_Data_Collection::SORT_ORDER_ASC);

        $rootNodes = array();
        $nodes = array();


        /** @var $item Xcom_Listing_Model_Category */
        foreach ($collection as $item) {
            $catId = $item->getData('id');
            if (isset($nodes[$catId])) {
                $node = $nodes[$catId];
            } else {
                $node = new stdClass();
                $nodes[$catId] = $node;
            }
            $leaf  = $item->getData('leaf_category');
            $count = $item->getData('children_count');
            $name  = $item->getData('name') . (!$leaf && !$recommended ? (' (' . (int) $count . ')') : '');
            $node->id           = $catId;
            $node->text         = $name;

            if ($selectedCategoryId == $catId && $leaf) {
                $node->checked  = true;
            }

            if ($leaf) {
                $node->leaf     = true;
            } else {
                $node->disabled = true;
            }
            $node->cls = $leaf ? 'file' : 'folder';

            /**
             * Add item to root when it's root item or level is set or parent ID is set
             */
            if ($item->getData('path') == $catId || $level || $this->_parentId) {
                $rootNodes[] = $node;
            } else {
                $nodes[$item->getData('parent_id')]->children[] = $node;
            }
        }

        /** @var $helper Mage_Core_Helper_Data */
        $helper = Mage::helper('core');
        return $helper->jsonEncode($rootNodes);
    }

    /**
     * Return collection
     *
     * @return Xcom_Listing_Model_Resource_Category_Collection
     */
    protected function _getCategoryCollection()
    {
        if (null === $this->_categoryCollection) {
            $this->_categoryCollection = Mage::getResourceModel('xcom_listing/category_collection');
        }
        return $this->_categoryCollection;
    }

    /**
     * Set default filters to collection
     *
     * @return Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_CategoriesTree
     */
    protected function _setDefaultCollectionFilters()
    {
        $this->_getCategoryCollection()->setOptions(array(
            'channeltypeCode' => $this->getCurrentChannel()->getChanneltypeCode(),
            'environmentName' => $this->getCurrentChannel()->getAuthEnvironment(),
            'siteCode'        => $this->getCurrentChannel()->getSiteCode(),
        ));
        return $this;
    }

    /**
     * Category ID
     *
     * @return string
     */
    public function getJsonUrl()
    {
        return $this->getUrl('*/*/categoriesJson');
    }

    /**
     * Set parent ID for categories list
     *
     * @param int $parentId
     * @return Xcom_Ebay_Block_Adminhtml_Product_Edit_Tab_Settings_CategoriesTree
     */
    public function setCategoriesParentId($parentId)
    {
        $this->_parentId = $parentId;
        return $this;
    }


    /**
     * Get recommended categories pairs - ID => Path IDs
     *
     * @return array
     */
    protected function _getRecommendedCategories()
    {
        if (null === $this->_recommendedCategories) {
            /** @var $helper Xcom_Ebay_Helper_Data */
            $helper = $this->helper('xcom_ebay');
            $attributeSetArray = $helper->prepareAttributeSetArray();
            $recommendedCategories = array();

            if (count($attributeSetArray) == 1) {
                /** @var $categoryResource Xcom_Listing_Model_Resource_Category */
                $categoryResource = Mage::getResourceModel('xcom_listing/category');
                $recommendedCategories = $categoryResource->getRecommendedCategories(
                    reset($attributeSetArray),
                    $this->getCurrentChannel()->getChanneltypeCode(),
                    $this->getCurrentChannel()->getAuthEnvironment(),
                    $this->getCurrentChannel()->getSiteCode(),
                    $this->getData('selected_category_id'));
            }
            $this->_recommendedCategories = $recommendedCategories;
        }
        return $this->_recommendedCategories;
    }

    /**
     * Is exist recommended categories?
     *
     * @return bool
     */
    public function hasRecommendedCategories()
    {
        return (bool) $this->_getRecommendedCategories();
    }

    /**
     * Retrieve current channel object.
     *
     * @return Xcom_Ebay_Model_Channel
     */
    public function getCurrentChannel()
    {
        if (null === $this->_channel) {
            $this->_channel = Mage::registry('current_channel');
        }
        return $this->_channel;
    }
}
