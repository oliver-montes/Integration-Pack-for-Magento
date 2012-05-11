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
class Xcom_Mmp_Model_Account extends Mage_Core_Model_Abstract
{
    /**
     * Initialize class.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('xcom_mmp/account');
    }

    /**
     * Retrieve account by unique environment and user_id pair.
     *
     * @param $environment
     * @param $user_id
     * @return Xcom_Mmp_Model_Account
     */
    public function loadAccount($environment, $user_id)
    {
        $accountId = $this->_getResource()->getAccountIdByUniqueKey($environment, $user_id);
        if ($accountId) {
            $this->load($accountId);
        }
        return $this;
    }

    /**
     * Validate account data.
     *
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function validate()
    {
        // Auth ID
        if ($this->_isFieldEmpty('auth_id', Zend_Validate_NotEmpty::STRING)) {
            $this->_throwException('Wrong account data. Auth ID is required.');
        }
        // xAccount ID
        if ($this->_isFieldEmpty('xaccount_id', Zend_Validate_NotEmpty::STRING)) {
            $this->_throwException('Wrong account data. xAccount ID is required.');
        }
        // User ID
        if ($this->_isFieldEmpty('user_id', Zend_Validate_NotEmpty::STRING)) {
            $this->_throwException('Wrong account data. User ID is required.');
        }
        // Validated At
        if ($this->_isFieldEmpty('validated_at', Zend_Validate_NotEmpty::STRING)) {
            $this->_throwException('Wrong account data. Validated At is required.');
        }
        // Environment
        if ($this->_isFieldEmpty('environment', Zend_Validate_NotEmpty::INTEGER)) {
            $this->_throwException('Wrong account data. Environment is required.');
        }

        return true;
    }

    /**
     * Returns TRUE if field is empty.
     * Returns FALSE if field value is filled.
     *
     * @param string $fieldName
     * @param int $type Zend_Validate_NotEmpty constant
     * @return bool
     */
    protected function _isFieldEmpty($fieldName, $type)
    {
        return !(Zend_Validate::is($this->getData($fieldName), 'NotEmpty', array(Zend_Validate_NotEmpty::NULL))
            && Zend_Validate::is($this->getData($fieldName), 'NotEmpty', array($type)));
    }

    /**
     * Throws Mage_Core_Exception exception.
     *
     * @param string $message
     */
    protected function _throwException($message)
    {
        Mage::throwException(Mage::helper('xcom_mmp')->__($message));
    }
}
