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
 * Class of Xcom Core Channel model
 *
 * @category   Xcom
 * @package    Xcom_Cse
 * @author     Magento Core Team <core@magentocommerce.com>
 * @method Xcom_Cse_Model_Resource_Channel_Collection getCollection()
 * @method Xcom_Cse_Model_Resource_Channel_Collection getResourceCollection()
 * @method Xcom_Cse_Model_Resource_Channel getResource()
 * @method Xcom_Cse_Model_Resource_Channel _getResource()
 * @method string getChanneltypeCode()
 * @method Xcom_Cse_Model_Channel setChanneltypeCode() setChanneltypeCode(string $channelTypeCode)
 * @method string getSiteCode()
 * @method Xcom_Cse_Model_Channel setSiteCode() setSiteCode(string $siteCode)
 * @method string getName()
 * @method Xcom_Cse_Model_Channel setName() setName(string $name)
 * @method int getStoreId()
 * @method Xcom_Cse_Model_Channel setStoreId() setStoreId(int $storeId)
 * @method int getIsActive()
 * @method Xcom_Cse_Model_Channel setIsActive() setIsActive(int $isActive)
 * @method int getAccountId()
 * @method Xcom_Cse_Model_Channel setAccountId() setAccountId(int $accountId)
 */
class Xcom_Cse_Model_Channel extends Mage_Core_Model_Abstract
{
    /**
     * @var Xcom_Cse_Model_Account
     */
    protected $_account;

    /**
     * Initialize class.
     * @return void
     */
    protected function _construct()
    {
        $this->_init('xcom_cse/channel');
    }

    /**
     * Validate channel.
     *
     * @throws Mage_Core_Exception
     * @return Xcom_Cse_Model_Channel
     */
    public function validate()
    {
        $helper = Mage::helper('xcom_cse');
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
            $messages[] = $helper->__('The Target Country is required.');
        }
        // Duplicate Name
        if (!$this->_getResource()->isChannelNameUnique($this)) {
            $messages[] = $helper->__('A channel with the same Name already exists.');
        }
        // Duplicate Offer Name
        if (!$this->_getResource()->isOfferNameUnique($this)) {
            $messages[] = $helper->__('A file with the same name already exists. Please choose a different filename.');
        }
        // Validate Offer Name extension
        if (!$this->_getResource()->isOfferNameHasValidExtension($this)) {
            $messages[] = $helper->__('Please enter a filename with .xml extension.');
        }
        // Validate Offer Name special characters
        if (!$this->_getResource()->isOfferNameHasValidCharacters($this)) {
            $messages[] = $helper->__('The Feed Filename cannot contain special characters (e.g. spaces, !, ", #, $, %, &, \', (, ), *, +, /, :, ;, <, >, ?, @, [, \, ], ^, {, |, } ).');
        } 
        if (!$this->getId() && !$this->_getResource()->isChannelStoreSiteUnique($this)) {
            $messages[] = $helper->__('A channel with such combination of Store View, '
                . 'Target Country and Google Account already exists.');
        }
        if ($messages) {
            throw Mage::exception('Mage_Core', implode('<br />', $messages));
        }
        return $this;
    }

    /**
     * Check if channel type is already authorized
     *
     * @return bool
     */
    public function isAuthorized()
    {
        return $this->getAccount()->getUserId() ? true : false;
    }

    /**
     * Instantiate account object.
     * Load account information in case if account_id is appeared.
     *
     * @return Xcom_Cse_Model_Account
     */
    public function getAccount()
    {
        if (is_null($this->_account) && $this->getAccountModelClass()) {
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
        return 'xcom_cse/account';
    }

    /**
     * Return authorization id if it exists
     *
     * @return int|null
     */
    public function getAuthId()
    {
        return $this->getAccount()->getAuthId();
    }
    
    /**
     * @param $xAccountId string
     * @param $siteId string
     * @param $offerName string
     * @return int|bool
     */
    public function getIdByKey($xAccountId, $siteId, $offerName)
    {
        return $this->_getResource()->getIdByKey($xAccountId, $siteId, $offerName);
    }
}
