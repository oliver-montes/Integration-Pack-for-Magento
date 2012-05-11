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
 * @package     Xcom_Mmp
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class of resource of account collection
 *
 * @category   Xcom
 * @package    Xcom_Mmp
 */
class Xcom_Mmp_Model_Resource_Account_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initialize resource model for collection
     *
     */
    public function _construct()
    {
        $this->_init('xcom_mmp/account');
    }

    /**
     * Add filter to select by channel type code
     *
     * @param string $channelTypeCode
     * @return Xcom_Mmp_Model_Resource_Account_Collection
     */
    public function addChanneltypeCodeFilter($channelTypeCode)
    {
        $this->addFieldToFilter('channeltype_code', $channelTypeCode);
        return $this;
    }

    /**
     * Add "Expired" value for Validation Period column.
     *
     * @return Xcom_Mmp_Model_Resource_Account_Collection
     */
    public function addValidationExpiredData()
    {
        $this->getSelect()
            ->columns(array(
                          'validation_expired'  => new Zend_Db_Expr('(CASE WHEN DATEDIFF(validated_at, NOW()) < 0' .
                                                                    ' THEN 1 WHEN validated_at=0 THEN 1 ELSE 0 END)')
                      ));
        return $this;
    }

    /**
     * Add date validation filter to collection
     *
     * @return Xcom_Mmp_Model_Resource_Account_Collection
     */
    public function addDateValidationFilter()
    {
        $this->addFieldToFilter(
            new Zend_Db_Expr('(CASE WHEN DATEDIFF(validated_at, NOW()) >= 0' .
                             ' THEN 1 WHEN validated_at=0 THEN 1 ELSE 0 END)')
            , 1);
        return $this;
    }
}
