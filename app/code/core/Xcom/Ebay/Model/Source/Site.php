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
class Xcom_Ebay_Model_Source_Site
{
    protected $_sites;

    /**
     * Retrieve option hash
     *
     * @param bool $withDefault
     * @param string $defaultLabel
     * @param string $defaultValue
     * @return array
     */
    public function toOptionHash($withDefault = true, $defaultLabel = '', $defaultValue = '')
    {
        $options = array();
        if ($withDefault) {
            $options[$defaultValue] = $defaultLabel;
        }

        foreach ($this->getSites() as $site) {
            $options[$site['site_code']] = Mage::helper('xcom_ebay')->__($site['name']);
        }

        return $options;
    }

    /**
     * Retrieve option array
     *
     * @param bool $withDefault
     * @param string $defaultLabel
     * @param string $defaultValue
     *
     * @return array
     */
    public function toOptionArray($withDefault = true, $defaultLabel = '', $defaultValue = '')
    {
        $options = array();
        if ($withDefault) {
            $options[] = array('value' => $defaultValue, 'label' => $defaultLabel);
        }
        foreach ($this->getSites() as $site) {
            $options[] = array(
               'value' => $site['site_code'],
               'label' => Mage::helper('xcom_ebay')->__($site['name'])
            );
        }

        return $options;
    }

    /**
     * Retrieve site list for channel type
     *
     * @return array
     */
    public function getSites()
    {
        if (null === $this->_sites) {
            if (!Mage::helper('xcom_ebay')->getChanneltypeCode()) {
                $this->_sites = array();
            }
            /** @var $site Xcom_Mmp_Model_Resource_Site */
            $site = Mage::getResourceModel('xcom_mmp/site');
            $this->_sites = $site->getSites(Mage::helper('xcom_ebay')->getChanneltypeCode());
        }
        return $this->_sites;
    }
}
