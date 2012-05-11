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
 * @package     Xcom_Cse
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Cse_Model_Resource_Site extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('xcom_cse/site', 'site_id');
    }

    /**
     * insert or update site data
     *
     * @param string $channelTypeCode
     * @param array $siteData
     *
     * @return Xcom_Cse_Model_Resource_Site
     */
    public function upgrade($channelTypeCode, $siteData)
    {
        $writeAdapter   = $this->_getWriteAdapter();

        $existedData = $this->getSiteCodes($channelTypeCode);
        $newData     = array();
        $insertData = array();
        foreach ($siteData as $data) {
            $insertData[] = array(
                'channel_type_code' => $channelTypeCode,
                'site_code'         => !empty($data['site_code']) ? $data['site_code'] : null,
                'name'              => !empty($data['name']) ? $data['name'] : null
            );
            $newData[]    = $data['site_code'];
        }
        $writeAdapter->insertOnDuplicate($this->getMainTable(), $insertData, array('name'));

        $oldData    = array_diff($existedData, $newData);
        if ($oldData) {
            $writeAdapter->delete($this->getMainTable(), array(
                $writeAdapter->quoteInto('channel_type_code = ?', $channelTypeCode),
                $writeAdapter->quoteInto('site_code IN(?)', $oldData)
            ));
        }
        return $this;
    }

    /**
     * Retrieve site collection to array for specified site
     *
     * @param string $channelTypeCode
     * @return array
     */
    public function getSiteCodes($channelTypeCode = null)
    {
        $readAdapter    = $this->_getReadAdapter();
        /** @var $select Zend_Db_Select */
        $select = $readAdapter->select()->from($this->getMainTable(), array('site_id','site_code'))
                ->where('channel_type_code=?', $channelTypeCode);
        return  $readAdapter->fetchPairs($select);
    }

    /**
     * Retrieve site collection to array for specified site
     *
     * @param string $channelTypeCode
     * @return array
     */
    public function getSites($channelTypeCode)
    {
        $readAdapter    = $this->_getReadAdapter();
        $select         = $readAdapter->select()
            ->from($this->getMainTable(), array('site_code', 'name'))
            ->where('channel_type_code=?', $channelTypeCode)
            ->order('name');
        return  $readAdapter->fetchAll($select);
    }
}
