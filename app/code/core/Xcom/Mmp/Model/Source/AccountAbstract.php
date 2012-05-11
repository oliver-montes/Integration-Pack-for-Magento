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
 * Abstract class of source model of accounts
 *
 * @category   Xcom
 * @package    Xcom_Mmp
 */
abstract class Xcom_Mmp_Model_Source_AccountAbstract
{
    /**
     * Options array data
     *
     * @var array
     */
    protected $_data;

    /**
     * Collection model with channel accounts
     *
     * @var Xcom_Mmp_Model_Resource_Account_Collection
     */
    protected $_collection;

    /**
     * Get account collection
     *
     * @return Xcom_Mmp_Model_Resource_Account_Collection
     */
    public function getCollection()
    {
        if (null === $this->_collection) {
            $this->_collection = Mage::getSingleton('xcom_mmp/account')->getResourceCollection();
        }
        return $this->_collection;
    }

    /**
     * Get options array data
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (null !== $this->_data) {
            return $this->_data;
        }

        $this->_data[] = array(
            'value' => '',
            'label' => Mage::helper('xcom_mmp')->__('Please Select One'),
        );

        $channelType = $this->getChannelTypeCode();
        if (!$channelType) {
            //Channel type code is empty
            return $this->_data;
        }

        $this->getCollection()
            ->addFieldToFilter('channeltype_code', $channelType)
            ->setOrder('user_id', Varien_Data_Collection::SORT_ORDER_ASC);

        /** @var $account Xcom_Mmp_Model_Account */
        foreach ($this->getCollection()->getAllIds() as $accountId) {
            $account = Mage::getModel('xcom_mmp/account')->load($accountId);

            $this->_data[] = array(
                'value'     => $account->getId(),
                'label'     => $account->getUserId() . ' (' . $account->getEnvironmentValue() . ')',
            );
        }

        return $this->_data;
    }

    /**
     * Get channel type code for a specific channel
     *
     * @abstract
     * @return string
     */
    abstract public function getChannelTypeCode();

    /**
     * Get key-value data
     *
     * @return array
     */
    public function toOptionHash()
    {
        $data = array();
        foreach ($this->toOptionArray() as $item) {
            $data[$item['value']] = $item['label'];
        }
        return $data;
    }

    /**
     * Set flag true for get only active items
     *
     * @return Xcom_Mmp_Model_Source_Channel_AccountAbstract
     */
    public function setActiveFilter()
    {
        $this->getCollection()
            ->addDateValidationFilter()
            ->addFieldToFilter('status', 1);
        return $this;
    }
}
