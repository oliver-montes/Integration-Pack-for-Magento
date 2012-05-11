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

class Xcom_Mmp_Model_Resource_HandlingTime extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('xcom_mmp/handling_time', 'handling_time_id');
    }


    /**
     * Data upgrade for handling time
     *
     * @param string $channelTypeCode
     * @param string $siteCode
     * @param string $environment
     * @param array $handlingTimeData
     *
     * @return Xcom_Mmp_Model_Resource_HandlingTime
     */
    public function upgrade($channelTypeCode, $siteCode, $environment, $handlingTimeData)
    {
        $writeAdapter   = $this->_getWriteAdapter();

        $existedData = $this->getMaxHandlingTimes($channelTypeCode, $siteCode, $environment);
        $newData     = array();
        $insertData = array();
        foreach ($handlingTimeData as $data) {
            $insertData[] = array(
                'channel_type_code' => $channelTypeCode,
                'site_code'         => $siteCode,
                'environment'       => $environment,
                'description'       => !empty($data['description']) ? $data['description'] : null,
                'max_handling_time' => isset($data['max_handling_time']) ? $data['max_handling_time'] : null
            );
            $newData[]    = $data['max_handling_time'];
        }
        $writeAdapter->insertOnDuplicate($this->getMainTable(), $insertData, array('description'));

        $oldData    = array_diff($existedData, $newData);
        if ($oldData) {
            $writeAdapter->delete($this->getMainTable(), array(
                $writeAdapter->quoteInto('channel_type_code = ?', $channelTypeCode),
                $writeAdapter->quoteInto('site_code = ?', $siteCode),
                $writeAdapter->quoteInto('environment = ?', $environment),
                $writeAdapter->quoteInto('max_handling_time IN(?)', $oldData)
            ));
        }
        return $this;
    }

    /**
     * Retrieve max_handling_time collection to array for specified site
     * TODO handling time collection for native toOptionHash might be needed
     *
     * @param string $channelTypeCode
     * @param string $siteCode
     * @param string $environment
     * @return array
     */
    public function getMaxHandlingTimes($channelTypeCode, $siteCode, $environment)
    {
        $readAdapter    = $this->_getReadAdapter();
        $select         = $readAdapter->select()
            ->from($this->getMainTable(), array('handling_time_id', 'max_handling_time'))
            ->where('channel_type_code = ?', $channelTypeCode)
            ->where('site_code = ?', $siteCode)
            ->where('environment = ?', $environment);

        return  $readAdapter->fetchPairs($select);
    }

    /**
     * Retrieve handling time collection for specified site
     * TODO handling time collection for native toOptionHash might be needed
     *
     * @param Xcom_Mmp_Model_Channel $channel
     * @return array
     */
    public function getHandlingTimes($channel)
    {
        $readAdapter    = $this->_getReadAdapter();
        $select         = $readAdapter->select()
            ->from($this->getMainTable(), array('max_handling_time', 'description'))
            ->where('channel_type_code=?', $channel->getChanneltypeCode())
            ->where('site_code=?', $channel->getSiteCode())
            ->where('environment=?', $channel->getAuthEnvironment())
            ->order('max_handling_time');
        return  $readAdapter->fetchAll($select);
    }
}
