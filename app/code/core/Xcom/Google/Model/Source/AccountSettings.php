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
 * @package     Xcom_Google
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class of source model of Google accounts
 *
 * @category   Xcom
 * @package    Xcom_Google
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Xcom_Google_Model_Source_AccountSettings extends Xcom_Cse_Model_Source_AccountAbstract
{
    /**
     * Get channel type code for the Google channel
     *
     * @return array
     */
    public function getChannelTypeCode()
    {
        /** @var $helper Xcom_Google_Helper_Data */
        $helper = Mage::helper('xcom_google');
        return $helper->getChanneltypeCode();
    }

    /**
     * Get options array data
     *
     * @return array
     */
    public function toOptionArray()
    {
        $this->_data = parent::toOptionArray();
        $this->_data[0] = array(
            'value' => '*',
            'label' => $this->_helper->__('All Google Accounts'),
        );

        array_unshift($this->_data, array(
            'value' => '',
            'label' => $this->_helper->__('Please Select Google Accounts'),
        ));
        return $this->_data;
    }
}
