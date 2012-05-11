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
class Xcom_Ebay_Model_Backend_Time_Current extends Mage_Core_Model_Config_Data
{
    /**
     * Returns current time in format hour,minute,second.
     *
     * @return string
     */
    public function getValue()
    {
        $value = Mage::getStoreConfig($this->getPath());

        if (!empty($value)) {
            return $value;
        }
        return Mage::getSingleton('core/date')->date('H,i,s');
    }

    /**
     * Save time in string format
     */
    protected function _beforeSave()
    {
        $this->setData('value', join(',', $this->getData('value')));
    }

}
