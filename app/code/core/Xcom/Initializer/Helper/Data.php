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
 * @package     Xcom_Initializer
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Initializer_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Check if all data is collected and ready to start work with xcom environment
     *
     * @return bool
     */
    public function isDataCollected()
    {
        $topicsAmount = count(Mage::helper('xcom_xfabric')->getNodeByXpath('*/*[@initializer="prepopulate"]'));
        return Mage::getResourceModel('xcom_initializer/job')->isDataCollected($topicsAmount);
    }

    /**
     * Verifies if all jobs for the provided topic were finished
     *
     * @param $topic
     * @return bool
     */
    public function isAllJobsFinished($topic)
    {
        return Mage::getResourceModel('xcom_initializer/job')
            ->isAllJobsFinished($topic);
    }
}
