<?php
/**
 *
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
 * @package     Xcom_Mmp
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Mmp_Model_Resource_Currency extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('xcom_mmp/currency', 'currency_id');
    }


    /**
     * Data upgrade for currency
     *
     * @param string $channelTypeCode
     * @param string $siteCode
     * @param array $currencyData
     *
     * @return Xcom_Mmp_Model_Resource_Currency
     */
    public function upgrade($channelTypeCode, $siteCode, $currencyData)
    {
        $writeAdapter   = $this->_getWriteAdapter();
        $insertData     = array();
        foreach ($currencyData as $currency) {
            $insertData[]   = array(
                'currency'          => $currency,
                'channel_type_code' => $channelTypeCode,
                'site_code'   => $siteCode,
            );
        }
        if ($insertData) {
            $writeAdapter->insertOnDuplicate($this->getMainTable(), $insertData);
            //remove old data
            $writeAdapter->delete($this->getMainTable(),
                array(
                    'currency NOT IN (?)'   => $currencyData,
                    'channel_type_code = ?' => $channelTypeCode,
                    'site_code = ?'   => $siteCode
                )
            );
        }
        return $this;
    }

    /**
     * Retrieve currency collection for specified site
     *
     * @param Xcom_Mmp_Model_Channel $channel
     * @return array
     */
    public function getCurrencies($channel)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('currency'))
            ->where('channel_type_code=?', $channel->getChanneltypeCode())
            ->where('site_code=?', $channel->getSiteCode())
            ->order('currency');
        return  $this->_getReadAdapter()->fetchCol($select);
    }
}
