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
 * @package     Xcom_Google
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Google_Model_Account extends Xcom_Cse_Model_Account
{
    /**
     * Retrieve InitAuthorizationMessage options.
     *
     * @return array
     */
    public function getInitAuthorizationMessageOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->getInitAuthorizationMessageData();
        }
        return $this->_options;
    }

    /**
     * Retrieve data for InitAuthorizationMessage.
     *
     * @return array
     */
    public function getInitAuthorizationMessageData()
    {
        $data = array(
            'returnURL'         => null,
            'cancelURL'         => null,
            'cseAccountId' 		=> $this->getData('user_id'),
            'guid'              => $this->getGuid(),
        	'targetLocation'	=> $this->getData('target_location')
        );
        return $data;
    }

    /**
     * Prepare response from InitAuthorizationMessage.
     *
     * @param $object
     * @return array|string
     */
    public function prepareInitAuthorizationMessageResponse($object)
    {
        if (!is_object($object)) {
            return '';
        }
        $result = array();
        $data = $object->getResponseData();
        if (!empty($data['errors'])) {
            $errors = array();
            foreach ($data['errors'] as $error) {
                $errors[] = sprintf("Error (%s) - %s", $error['code'], $error['message']);
            }
            $result['error'] = implode("\n", $errors);
        } else {
            if (!empty($data['xAccountId'])) {
				$result['auth_id'] = $data['xAccountId'];
            }
        }
        return count($result) ? $result : '';
    }

    /**
     * Validate account.
     *
     * @return bool
     */
    public function validate()
    {
        // Account ID
        if (!Zend_Validate::is($this->getData('user_id'), 'NotEmpty', array(Zend_Validate_NotEmpty::NULL))) {
            Mage::throwException(Mage::helper('xcom_cse')->__('Wrong account data. Account ID is required.'));
        }
        if (!Zend_Validate::is($this->getData('user_id'), 'NotEmpty', array(Zend_Validate_NotEmpty::STRING))) {
            Mage::throwException(Mage::helper('xcom_cse')->__('Wrong account data. Account ID is required.'));
        }

        // Bucketname
        if (!Zend_Validate::is($this->getData('target_location'), 'NotEmpty', array(Zend_Validate_NotEmpty::NULL))) {
            Mage::throwException(Mage::helper('xcom_cse')->__('Wrong account data. Google Storage bucket name is required.'));
        }
        if (!Zend_Validate::is($this->getData('target_location'), 'NotEmpty', array(Zend_Validate_NotEmpty::STRING))) {
            Mage::throwException(Mage::helper('xcom_cse')->__('Wrong account data. Google Storage bucket name is required.'));
        }
        
        return true;
    }
}
