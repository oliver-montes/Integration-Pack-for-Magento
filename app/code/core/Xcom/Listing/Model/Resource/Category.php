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
class Xcom_Listing_Model_Resource_Category extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Relation table name.
     *
     * @var string
     */
    protected $_productTypeTable;

    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('xcom_listing/category', 'category_id');
        $this->_productTypeTable = $this->getTable('xcom_listing/category_product_type');
    }

    /**
     * Get recommended categories IDs
     *
     * @param int $productTypeId
     * @return array
     */
    public function getRecommendedCategoryIds($productTypeId)
    {
        return $this->_getReadAdapter()->fetchCol($this->_getReadAdapter()->select()
                    ->from($this->_productTypeTable, array('category_id'))
                    ->where("mapping_product_type_id = ?", $productTypeId));
    }

    /**
     * Retrieve recommended ids
     *
     * @param int $attributeSetId           Attribute set ID with recommended category IDs
     * @param string $marketplace           Marketplace channel type code
     * @param string $environmentName       Environment name
     * @param string $siteCode              Site code name
     * @param int|null $selectedCategoryId  Join selected category ID like a recommended
     * @return array
     */
    public function getRecommendedCategories($attributeSetId, $marketplace, $environmentName,
                                             $siteCode, $selectedCategoryId = null)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(array('main_table' => $this->getMainTable()), array('id', 'path'))
            ->joinLeft(array('cpt' => $this->getTable('xcom_listing/category_product_type')),
            'main_table.category_id = cpt.category_id',
            array())
            ->joinLeft(array('mptr' => $this->getTable('xcom_mapping/product_type_relation')),
                'cpt.mapping_product_type_id = mptr.mapping_product_type_id')
            ->where('main_table.environment_name = ?', $environmentName)
            ->where('main_table.marketplace = ?', $marketplace)
            ->where('main_table.site_code = ?', $siteCode);

        $condition = $adapter->quoteInto('mptr.attribute_set_id = ?', $attributeSetId, Zend_Db::INT_TYPE);
        if ($selectedCategoryId) {
            $condition .= $adapter->quoteInto(' OR main_table.id = ?', $selectedCategoryId, Zend_Db::INT_TYPE);
        }
        $select->where($condition);

        return $adapter->fetchPairs($select);
    }

    /**
     * cleanup
     *
     * @param array $existingIds
     * @param string $marketPlace
     * @param string $siteCode
     * @param string $environmentName
     * @return Xcom_Listing_Model_Resource_Category
     */
    public function clean(array $existingIds, $marketPlace, $siteCode, $environmentName)
    {
        //clean collection cache
        Mage::app()->getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array(Xcom_Listing_Model_Resource_Category_Collection::XCOM_CACHE_TAG));

        $this->_getWriteAdapter()->delete($this->getMainTable(), array(
            'environment_name = ?' => $environmentName,
            'site_code = ?' => $siteCode,
            'marketplace = ?' => $marketPlace,
            'id NOT IN(?)' => $existingIds
        ));

        return $this;
    }

    /**
     * Bulk Categories import
     *
     * @param array $categories
     * @return Xcom_Listing_Model_Resource_Category
     */
    public function import(array $categories)
    {
        foreach (array_chunk($categories, 300) as $chunk) {
            $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $chunk);
        }
        return $this;
    }

    /**
     * Imports and cleans up relations for product type
     *
     * @param $productTypeId
     * @param $categoryIds
     * @param $marketplace
     * @param $siteCode
     * @param $environment
     * @return Xcom_Listing_Model_Resource_Category
     */
    public function importRelations($productTypeId, $categoryIds, $marketplace,  $siteCode, $environment)
    {
        $adapter    = $this->_getWriteAdapter();
        /** @var $delete Varien_Db_Select */
        $delete = $adapter->select()
            ->from(array('cpt' => $this->_productTypeTable), array())
            ->joinLeft(array('xcc' => $this->getMainTable()), 'xcc.category_id = cpt.category_id', array())
            ->where('xcc.environment_name = ?', $environment)
            ->where('xcc.site_code = ?', $siteCode)
            ->where('xcc.marketplace = ?', $marketplace)
            ->where('xcc.id IN ( ? )', $categoryIds)
            ->where('cpt.mapping_product_type_id = ?', $productTypeId)
            ->where('cpt.category_id IS NULL');
        $adapter->query($delete->deleteFromSelect('cpt'));
        /** @var $insert Varien_Db_Select */
        $insert = $adapter->select()
            ->from(array('cpt' => $this->getMainTable()), array())
            ->where('cpt.environment_name = ?', $environment)
            ->where('cpt.site_code = ?', $siteCode)
            ->where('cpt.marketplace = ?', $marketplace)
            ->where('cpt.id IN ( ? )', $categoryIds)
            ->columns(array(
                'category_id'              => 'cpt.category_id',
                'mapping_product_type_id'  => new Zend_Db_Expr($productTypeId)
            ));
        $adapter->query($insert->insertFromSelect($this->_productTypeTable,
            array('category_id', 'mapping_product_type_id'), true));
        return $this;
    }

    /**
     * Retrieve category name
     *
     * @param $categoryId
     * @param $marketplace
     * @param $siteCode
     * @param $accountId
     * @return string
     */
    public function getNameFromCategory($categoryId, $marketplace, $siteCode, $accountId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(array('xma' => $this->getTable('xcom_mmp/account')), array('main_table.name'))
            ->joinLeft(array('xme' => $this->getTable('xcom_mmp/environment')),
                'xme.environment_id = xma.environment')
             ->joinLeft(array('main_table' => $this->getMainTable()),
                'xme.environment = main_table.environment_name')
            ->where('main_table.id = ?', (int)$categoryId)
            ->where('main_table.site_code = ?', $siteCode)
            ->where('main_table.marketplace = ?', $marketplace)
            ->where('xma.account_id = ?', $accountId);
        return $adapter->fetchOne($select);
    }
}
