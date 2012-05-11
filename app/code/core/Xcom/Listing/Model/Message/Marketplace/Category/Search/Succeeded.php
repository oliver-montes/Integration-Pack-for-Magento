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
 * to license
 * @magentocommerce.com so we can send you a copy immediately.
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

class Xcom_Listing_Model_Message_Marketplace_Category_Search_Succeeded extends Xcom_Xfabric_Model_Message_Response
{
    /**
     * Categories array
     *
     * @var array
     */
    protected $_categories = array();

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = '';

    /**
     * Message will be processed later to reduce the time on receiving
     * @var bool
     */
    protected $_isProcessLater = true;

    protected function _construct()
    {
        $this->_topic = 'marketplace/category/searchSucceeded';
        $this->_schemaRecordName = 'SearchCategoriesSucceeded';
        parent::_construct();
    }

    /**
     * Contains all actions that need to be performed once message is received
     *
     * @return Xcom_Xfabric_Model_Message_Response
     */
    public function process()
    {
        //since this process is delayed event prefix has to be set here
        $this->_eventPrefix = 'response_message';
        set_time_limit(0);
        $data = $this->getBody();
        $this->_categories = array();
        $marketPlace = $data['marketplace'];
        $siteCode    = $data['siteCode'];
        $environment = $data['environmentName'];
        foreach ($data['categories'] as &$categoryData) {
            $this->_categories[$categoryData['id']] = array(
                'id'                => $categoryData['id'],
                'name'              => $categoryData['name'],
                'parent_id'         => $categoryData['parentId'],
                'leaf_category'     => $categoryData['leafCategory'],
                'level'             => $categoryData['categoryLevel'],
                'children_count'    => 0,
                'catalog_enabled'   => $categoryData['catalogEnabled'],
                'marketplace'       => $marketPlace,
                'site_code'         => $siteCode,
                'environment_name'  => $environment,
            );
        }
        unset($data);
        // Name generation
        foreach ($this->_categories as &$category) {
            // _processCategory calls recursively
            // so we check path to exclude double call of method
            if (!isset($category['path'])) {
                $this->_processCategory($category);
            }
        }
        // Actual saving
        /** @var $resourceCategory Xcom_Listing_Model_Resource_Category */
        $resourceCategory = Mage::getResourceModel('xcom_listing/category');
        $resourceCategory
            ->import($this->_categories)
            ->clean(array_keys($this->_categories), $marketPlace, $siteCode, $environment);
        $this->_categories = null;
        return parent::process();
    }

    /**
     * Process category
     *
     * @param $category
     * @return mixed
     */
    protected function _processCategory(&$category)
    {
        if ($category['parent_id'] != $category['id']) {
            if (!isset($this->_categories[$category['parent_id']]['path'])) {
                $this->_processCategory($this->_categories[$category['parent_id']]);
            }
            $this->_categories[$category['parent_id']]['children_count']++;
            $category['path'] = $this->_categories[$category['parent_id']]['path'] . '/' . $category['id'];
        } else {
            $category['path'] = $category['id'];
        }
    }
}
