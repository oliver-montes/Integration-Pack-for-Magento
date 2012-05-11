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

class Xcom_Mmp_Model_Resource_Country extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('xcom_mmp/country', 'country_id');
    }

    /**
     * insert or update country data
     *
     * @param string $channelTypeCode
     * @param string $siteCode
     * @param string $environment
     * @param array $countryData
     *
     * @return Xcom_Mmp_Model_Resource_Country
     */
    public function upgrade($channelTypeCode, $siteCode, $environment, $countryData)
    {
        $writeAdapter   = $this->_getWriteAdapter();

        $existedData = $this->getCountryCodes($channelTypeCode, $siteCode, $environment);
        $newData     = array();
        $insertData = array();
        foreach ($countryData as $data) {
            $insertData[] = array(
                'channel_type_code' => $channelTypeCode,
                'site_code'     => $siteCode,
                'environment'   => $environment,
                'description'   => !empty($data['description']) ? $data['description'] : null,
                'country_code'  => !empty($data['country_code']) ? $data['country_code'] : null
            );
            $newData[]    = $data['country_code'];
        }
        $writeAdapter->insertOnDuplicate($this->getMainTable(), $insertData, array('description'));

        $oldData    = array_diff($existedData, $newData);
        if ($oldData) {
            $writeAdapter->delete($this->getMainTable(), array(
                $writeAdapter->quoteInto('channel_type_code = ?', $channelTypeCode),
                $writeAdapter->quoteInto('site_code = ?', $siteCode),
                $writeAdapter->quoteInto('environment = ?', $environment),
                $writeAdapter->quoteInto('country_code IN(?)', $oldData)
            ));
        }
        return $this;
    }

    /**
     * Retrieve country collection to array for specified site
     *
     * @param string $channelTypeCode
     * @param string $siteCode
     * @param string $environment
     * @return array
     */
    public function getCountryCodes($channelTypeCode, $siteCode, $environment)
    {
        $readAdapter    = $this->_getReadAdapter();
        $select         = $readAdapter->select()
            ->from($this->getMainTable(), array('country_id', 'country_code'))
            ->where('channel_type_code=?', $channelTypeCode)
            ->where('site_code=?', $siteCode)
            ->where('environment=?', $environment);
        return  $readAdapter->fetchPairs($select);
    }

    /**
     * Retrieve country collection to array for specified site
     *
     * @param Xcom_Mmp_Model_Channel $channel
     * @return array
     */
    public function getCountries($channel)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('country_code', 'description'))
            ->where('channel_type_code=?', $channel->getChanneltypeCode())
            ->where('site_code=?', $channel->getSiteCode())
            ->where('environment=?', $channel->getAuthEnvironment())
            ->order('description');

        return $this->_getReadAdapter()->fetchPairs($select);
    }
}
