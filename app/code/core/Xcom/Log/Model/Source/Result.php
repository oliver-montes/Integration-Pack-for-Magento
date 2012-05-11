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
 * @package     Xcom_Log
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Source model for log results
 */
class Xcom_Log_Model_Source_Result
{
    /**
     * Warning result value
     */
    const RESULT_WARNING   = 'warning';

    /**
     * Error result value
     */
    const RESULT_ERROR     = 'error';

    /**
     * Success result value
     */
    const RESULT_SUCCESS   = 'success';

    /**
     * Retrieve options hash.
     *
     * @return array
     */
    public function toOptionHash()
    {
        $optionArray = array();
        $optionArray[self::RESULT_WARNING] = Mage::helper('xcom_log')->__('Warning');
        $optionArray[self::RESULT_ERROR]   = Mage::helper('xcom_log')->__('Error');
        $optionArray[self::RESULT_SUCCESS] = Mage::helper('xcom_log')->__('Success');
        return $optionArray;
    }
}
