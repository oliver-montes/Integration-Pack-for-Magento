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
 * Class of Xcom Core Channel model
 *
 * @category   Xcom
 * @package    Xcom_Mmp
 * @author     Magento Core Team <core@magentocommerce.com>
 * @method Xcom_Mmp_Model_Resource_Channel_Collection getCollection()
 * @method Xcom_Mmp_Model_Resource_Channel_Collection getResourceCollection()
 * @method Xcom_Mmp_Model_Resource_Channel getResource()
 * @method Xcom_Mmp_Model_Resource_Channel _getResource()
 * @method string getChanneltypeCode()
 * @method Xcom_Mmp_Model_Channel setChanneltypeCode() setChanneltypeCode(string $channelTypeCode)
 * @method string getSiteCode()
 * @method Xcom_Mmp_Model_Channel setSiteCode() setSiteCode(string $siteCode)
 * @method string getCode()
 * @method Xcom_Mmp_Model_Channel setCode() setCode(string $code)
 * @method string getName()
 * @method Xcom_Mmp_Model_Channel setName() setName(string $name)
 * @method int getStoreId()
 * @method Xcom_Mmp_Model_Channel setStoreId() setStoreId(int $storeId)
 * @method int getSortOrder()
 * @method Xcom_Mmp_Model_Channel setSortOrder() setSortOrder(int $sortOrder)
 * @method int getIsActive()
 * @method Xcom_Mmp_Model_Channel setIsActive() setIsActive(int $isActive)
 * @method int getAccountId()
 * @method Xcom_Mmp_Model_Channel setAccountId() setAccountId(int $accountId)
 */
class Xcom_Mmp_Model_Channel extends Mage_Core_Model_Abstract
{
    /**
     * @var Xcom_Mmp_Model_Account
     */
    protected $_account;

    /**
     * Initialize class.
     * @return void
     */
    protected function _construct()
    {
        $this->_init('xcom_mmp/channel');
    }

    /**
     * Validate channel.
     *
     * @throws Mage_Core_Exception
     * @return Xcom_Mmp_Model_Channel
     */
    public function validate()
    {
        $helper = Mage::helper('xcom_mmp');
        $messages = array();
        // Name
        if (!Zend_Validate::is($this->getName(), 'NotEmpty', array(Zend_Validate_NotEmpty::STRING))) {
            $messages[] = $helper->__('The Name is required.');
        }
        // Store View
        if (!Zend_Validate::is((int)$this->getStoreId(), 'NotEmpty', array(Zend_Validate_NotEmpty::INTEGER))) {
            $messages[] = $helper->__('The Store View is required.');
        }
        // Site
        if (!Zend_Validate::is($this->getSiteCode(), 'NotEmpty', array(Zend_Validate_NotEmpty::STRING))) {
            $messages[] = $helper->__('The Site is required.');
        }
        // Duplicate Name
        if (!$this->_getResource()->isChannelNameUnique($this)) {
            $messages[] = $helper->__('The channel with the same Name already exist.');
        }

        if (!$this->getId() && !$this->_getResource()->isChannelStoreSiteUnique($this)) {
            $messages[] = $helper->__('The Channel with such combination of Store View, '
                . 'eBay Site and eBay Account already exists.');
        }
        if ($this->_getResource()->isChannelStoreViewDiffers($this)) {
            $messages[] = $helper->__('You already have a StoreView associated with this eBay Account and '
                . 'eBay Site combination. Please select a different eBay Account Or eBay Site.');

        }
        if ($messages) {
            throw Mage::exception('Mage_Core', implode('<br />', $messages));
        }
        return $this;
    }

    /**
     * Instantiate account object.
     * Load account information in case if account_id is appeared.
     *
     * @return Xcom_Mmp_Model_Account
     */
    public function getAccount()
    {
        if (null === $this->_account && $this->getAccountModelClass()) {
            $this->_account = Mage::getModel($this->getAccountModelClass());
            if ($this->hasAccountId()) {
                $this->_account->load((int)$this->getAccountId());
            }
        }
        return $this->_account;
    }

    /**
     * @return string
     */
    public function getAccountModelClass()
    {
        return 'xcom_mmp/account';
    }

    /**
     * Return authorization id if it exists
     *
     * @return int|null
     */
    public function getXaccountId()
    {
        return $this->getAccount()->getXaccountId();
    }

    /**
     * Return authorization environment
     *
     * @return int|null
     */
    public function getAuthEnvironment()
    {
        return $this->getAccount()->getEnvironmentValue();
    }

    /**
     * @param $xProfileId string
     * @return int|bool
     */
    public function getIdByXProfileId($xProfileId)
    {
        return $this->_getResource()->getIdByXProfileId($xProfileId);
    }
}
