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
 * @package     Xcom_ChannelGroup
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_ChannelGroup_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get first sorted store ID
     *
     * @return int
     */
    public function getFirstStoreId()
    {
        //get first store
        $websites = Mage::app()->getWebsites();
        /** @var $website Mage_Core_Model_Website */
        $website = array_shift($websites);
        $groups = $website->getGroups();
        /** @var $group Mage_Core_Model_Store_Group */
        $group = array_shift($groups);
        $stores = $group->getStores();
        /** @var $store Mage_Core_Model_Store */
        $store = array_shift($stores);
        return $store->getId();
    }
}
