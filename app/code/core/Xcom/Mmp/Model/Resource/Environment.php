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

class Xcom_Mmp_Model_Resource_Environment extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('xcom_mmp/environment', 'environment_id');
    }

    /**
     * Upgrade 'xcom_mmp_environment' table
     *
     * @param $channelTypeCode
     * @param $siteCode
     * @param $environmentData
     * @return Xcom_Mmp_Model_Resource_Environment
     */
    public function upgrade($channelTypeCode, $siteCode, $environmentData)
    {
        $writeAdapter   = $this->_getWriteAdapter();
        $insertData     = array();
        $environmentName = array();
        foreach ($environmentData as $environment) {
            $insertData[]   = array(
                'environment'       => $environment['name'],
                'channel_type_code' => $channelTypeCode,
                'site_code'   => $siteCode,
            );
            $environmentName[] = $environment['name'];
        }
        if ($insertData) {
            $writeAdapter->insertOnDuplicate($this->getMainTable(), $insertData);
            //remove old data
            $writeAdapter->delete($this->getMainTable(),
                array(
                    'environment NOT IN (?)' => $environmentName,
                    'channel_type_code = ?'  => $channelTypeCode,
                    'site_code = ?'    => $siteCode
                )
            );
        }
        return $this;
    }


    /**
     * Returns all available environmentNames and siteCodes
     *
     * @return array
     */
    public function getAllEnvironments()
    {
        $readAdapter    = $this->_getReadAdapter();
        $select = $readAdapter->select()->from($this->getMainTable());
        return  $readAdapter->fetchAssoc($select);
    }
}
