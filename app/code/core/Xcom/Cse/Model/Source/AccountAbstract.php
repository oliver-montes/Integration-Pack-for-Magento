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
 * @package     Xcom_Cse
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract class of source model of accounts
 *
 * @category   Xcom
 * @package    Xcom_Cse
 */
abstract class Xcom_Cse_Model_Source_AccountAbstract
{
    /**
     * Helper
     *
     * @var Xcom_Cse_Helper_Data
     */
    protected $_helper;

    /**
     * Channel model
     *
     * @var Xcom_Cse_Model_Channel
     */
    protected $_channel;

    /**
     * Options array data
     *
     * @var array
     */
    protected $_data;

    /**
     * Constructor
     *
     * @param $options
     * @throws Exception    Throw exception if model channel is not set
     */
    public function __construct($options = array())
    {
        $this->_helper = Mage::helper('xcom_cse');
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

        // When more than one account is supported, uncomment.
        //$this->_data[] = array(
            //'value' => '',
            //'label' => $this->_helper->__('Please Select One'),
        //);

        $channelType = $this->getChannelTypeCode();
        if (!$channelType) {
            //Channel type code is empty
            return $this->_data;
        }

        /** @var $accountCollection Xcom_Cse_Model_Resource_Account_Collection */
        $accountCollection = Mage::getResourceModel('xcom_cse/account_collection');

        $accountCollection
            ->addFieldToFilter('channeltype_code', $channelType)
            ->setOrder('user_id', Varien_Data_Collection::SORT_ORDER_ASC);

        /** @var $account Xcom_Cse_Model_Account */
        foreach ($accountCollection as $account) {
            $this->_data[] = array(
                'value'     => $account->getId(),
                'label'     => $account->getUserId(),
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
     * Set channel model
     *
     * @param Xcom_Cse_Model_Channel $channel
     * @return Xcom_Cse_Model_Source_AccountAbstract
     */
    public function setChannel(Xcom_Cse_Model_Channel $channel)
    {
        $this->_channel = $channel;
        return $this;
    }
}
