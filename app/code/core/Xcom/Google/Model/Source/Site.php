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
class Xcom_Google_Model_Source_Site
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
        $sites = $this->getSites();
        $options = array();

        if ($withDefault) {
            $options[$defaultValue] = $defaultLabel;
        }

        foreach ($sites as $site) {
            $options[$site['site_code']] = Mage::helper('xcom_google')->__($site['name']);
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
        $sites = $this->getSites();
        $options = array();
        foreach ($sites as $site) {
            $options[] = array(
               'value' => $site['site_code'],
               'label' => Mage::helper('xcom_google')->__($site['name'])
            );
        }

        if ($withDefault) {
            array_unshift($options, array('value' => $defaultValue, 'label' => $defaultLabel));
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
        if (!$this->_sites) {
            //get data from DB. If it is empty then send request to xFabric
            $sites = $this->_getSitesByChannelType();
            if (!$sites) {
                $options = array(
                    'country'        => '*'
                );
                //response data must be saved to DB
                Mage::helper('xcom_xfabric')->send('marketplace/site/search', $options);
                //retrieve data from DB
                $sites = $this->_getSitesByChannelType();
            }

            $this->_sites = $sites;
        }
        return $this->_sites;
    }

    /**
     * Retrieve site data from DB for special channel type
     *
     * @return array
     */
    protected function _getSitesByChannelType()
    {
        if (!Mage::helper('xcom_google')->getChanneltypeCode()) {
            return array();
        }
        /** @var $country Xcom_Cse_Model_Resource_Site */
        $site = Mage::getResourceModel('xcom_cse/site');
        return $site->getSites(Mage::helper('xcom_google')->getChanneltypeCode());
    }
}
