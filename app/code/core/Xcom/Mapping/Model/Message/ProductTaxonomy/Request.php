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
 * @package     Xcom_Mapping
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Mapping_Model_Message_ProductTaxonomy_Request extends Xcom_Xfabric_Model_Message_Request
{
    /**
     * @param Varien_Object $data
     * @return array|null
     */
    protected function _getLocaleRecord(Varien_Object $data)
    {
        if ($data->hasData('language') && $data->hasData('country')) {
            return array(
                'language'  => (string)$data->getData('language'),
                'country'   => (string)$data->getData('country'),
                'variant'   => null
            );
        }
        return null;
    }
}
