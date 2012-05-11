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

class Xcom_Mmp_Model_Resource_ShippingService extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('xcom_mmp/shipping_service', 'shipping_id');
    }

    /**
     * insert or update shipping service data
     *
     * @param string $channelTypeCode
     * @param string $siteCode
     * @param string $environment
     * @param array $shippingData
     *
     * @return Xcom_Mmp_Model_Resource_Country
     */
    public function upgrade($channelTypeCode, $siteCode, $environment, $shippingData)
    {
        $writeAdapter   = $this->_getWriteAdapter();

        //keep names all saved/updated shipping services
        $shippingNames  = array();
        $insertData   = array();
        foreach ($shippingData as $num => $shippingService) {
            $insertData[$num] = $shippingService;
            $insertData[$num]['channel_type_code']   = $channelTypeCode;
            $insertData[$num]['site_code']           = $siteCode;
            $insertData[$num]['environment']         = $environment;
            $shippingNames[] = $shippingService['service_name'];

        }
        if ($insertData) {
            $writeAdapter->insertOnDuplicate($this->getMainTable(), $insertData);
        }

        $writeAdapter->delete($this->getMainTable(),
            array(
                'service_name NOT IN (?)'   => $shippingNames,
                'channel_type_code = ?'     => $channelTypeCode,
                'site_code = ?'             => $siteCode,
                'environment = ?'           => $environment
            )
        );
        return $this;
    }

    /**
     * @param Xcom_Mmp_Model_Channel $channel
     * @return array
     */
    public function getShippingServices(Xcom_Mmp_Model_Channel $channel)
    {
        $select = $this->_getShippingServiceSelectSql($channel);
        return $this->_getReadAdapter()->fetchAll($select);
    }

    /**
     * @param Xcom_Mmp_Model_Channel $channel
     * @return Zend_Db_Select
     */
    protected function _getShippingServiceSelectSql(Xcom_Mmp_Model_Channel $channel)
    {
        return $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('channel_type_code=?', $channel->getChanneltypeCode())
            ->where('site_code=?', $channel->getSiteCode())
            ->where('environment=?', $channel->getAuthEnvironment())
            ->order('service_name');
    }
}
