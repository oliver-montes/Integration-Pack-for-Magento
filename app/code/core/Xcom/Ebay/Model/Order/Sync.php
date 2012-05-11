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
 * @package     Xcom_ChannelOrder
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Ebay_Model_Order_Sync
{
    /**
     * Maximum date range in days
     */
    const DAY_INTERVAL = 30;

    /**
     * Valid date/time format
     */
    const VALID_FORMAT = 'MM/dd/YY HH:mm:ss';

    /**
     * Params with dates
     *
     * @var array
     */
    protected $_params;

    /**
     * Date model
     *
     * @var Zend_Date
     */
    protected $_startDate;

    /**
     * @var Zend_Date
     */
    protected $_endDate;

    /**
     * Set params
     *
     * @param array $params
     * @return Xcom_Ebay_Model_Order_Sync
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
        return $this;
    }

    /**
     * Check and return true if date interval less then DAY_INTERVAL
     *
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function validate()
    {
        /** @var $helper Xcom_Ebay_Helper_Data */
        $helper = Mage::helper('xcom_ebay');

        if (!$this->_isValidParams()) {
            throw Mage::exception('Mage_Core', $helper->__('All fields should be filled.'));
        }

        $firstDate =
            $this->_params['start_date'] . ' '  .
            $this->_params['start_time_hour'] . ':' .
            $this->_params['start_time_minute'] . ':' .
            $this->_params['start_time_seconds'];
        $endDate =
            $this->_params['end_date'] . ' '  .
            $this->_params['end_time_hour'] . ':' .
            $this->_params['end_time_minute'] . ':' .
            $this->_params['end_time_seconds'];

        if (!$this->_isDateValid($firstDate) || !$this->_isDateValid($endDate)) {
            throw Mage::exception('Mage_Core',
                $helper->__('Time format is not valid. Use format %s.', self::VALID_FORMAT));
        }

        $endDateTmp = new Zend_Date($endDate, self::VALID_FORMAT);
        if ($endDateTmp->getTimestamp() > time()) {
            throw Mage::exception('Mage_Core', $helper->__('End date can not exceed current date.'));
        }

        unset($endDateTmp);

        $this->_startDate   = Mage::app()->getLocale()->utcDate(null, $firstDate, true, self::VALID_FORMAT);
        $this->_endDate     = Mage::app()->getLocale()->utcDate(null, $endDate, false, self::VALID_FORMAT);

        $compareDate = clone $this->_startDate;
        $days = $this->_convertToDays($compareDate->sub($this->_endDate)->toValue());

        if ($days > 0) {
            throw Mage::exception('Mage_Core', $helper->__('The From date should not be later than the To date.'));
        }

        if (abs($days) > self::DAY_INTERVAL) {
            throw Mage::exception('Mage_Core',
                $helper->__('The maximum date range that may be specified is %s days.', self::DAY_INTERVAL));
        }
        return true;
    }

    /**
     * Validate incoming params
     *
     * @return bool
     */
    protected function _isValidParams()
    {
        if (!isset($this->_params['account']) || !isset($this->_params['start_date'])
            || !isset($this->_params['start_time_hour']) ||
            !isset($this->_params['start_time_minute']) || !isset($this->_params['start_time_seconds']) ||
            !isset($this->_params['end_date']) || !isset($this->_params['end_time_hour'])
            || !isset($this->_params['end_time_minute']) ||
            !isset($this->_params['end_time_seconds'])
        ) {
            return false;
        }
        return true;
    }

    /**
     * Convert seconds to days
     *
     * @param int $timestamp
     * @return float
     */
    protected function _convertToDays($timestamp)
    {
        return $timestamp/60/60/24;
    }

    /**
     * Validation date format
     *
     * @param $date
     * @return bool
     */
    protected function _isDateValid($date)
    {
        $validator = new Zend_Validate_Date(array('format' => self::VALID_FORMAT));
        return $validator->isValid($date);
    }

    /**
     * Send synchronize data
     *
     * @return Xcom_Ebay_Model_Order_Sync
     */
    public function send()
    {
        $this->validate();
        $accountId = Mage::getModel('xcom_mmp/account')->load((int) $this->_params['account'])->getXaccountId();
        /** @var $channelCollection Xcom_Mmp_Model_Resource_Channel_Collection */
        $channelCollection = Mage::getModel('xcom_mmp/channel')->getCollection()
            ->addChanneltypeCodeFilter('ebay') //TODO
            ->addFieldToFilter('main_table.account_id', (int)$this->_params['account']);
        $options = array(
            'seller_account_id' => $accountId,
            'from_date' => $this->_startDate->toString(Zend_Date::ATOM),
            'to_date'   => $this->_endDate->toString(Zend_Date::ATOM),
            'source_id' => 'eBay' //TODO
        );
        /** @var $helper Xcom_Xfabric_Helper_Data */
        $helper = Mage::helper('xcom_xfabric');

        /** @var $channel Xcom_Mmp_Model_Channel */
        foreach ($channelCollection as $channel) {
            $options['site_code'] = $channel->getSiteCode();
            $options['number_items'] = null;
            $options['start_item_index'] = null;
            $options['number_items_found'] = null;
            $helper->send('marketplace/order/search', $options);
        }

        return $this;
    }
}
