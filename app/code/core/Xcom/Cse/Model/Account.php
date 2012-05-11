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

class Xcom_Cse_Model_Account extends Mage_Core_Model_Abstract
{
    /**
     * Current message options.
     *
     * @var array
     */
    protected $_options;

    /**
     * Current unique identifier.
     *
     * @var string
     */
    protected $_guid;

    /**
     * Initialize class.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('xcom_cse/account');
    }

    /**
     * Retrieve unique identifier.
     *
     * @return string
     */
    public function getGuid()
    {
        if (!$this->_guid) {
            $this->_guid = Mage::helper('core')->uniqHash();
        }
        return $this->_guid;
    }

    /**
     * Send InitAuthorizationMessage.
     *
     * @return string
     */
    public function sendInitAuthorizationMessage()
    {
        $response = Mage::helper('xcom_xfabric')
          ->send('cse/authorization/init', $this->getInitAuthorizationMessageOptions());

        return $this->prepareInitAuthorizationMessageResponse($response);
    }

    /**
     * Retrieve account by unique user_id.
     *
     * @param $user_id
     * @return Xcom_Cse_Model_Account
     */
    public function loadAccount($user_id)
    {
        $accountId = $this->_getResource()->getAccountIdByUniqueKey($user_id);
        if ($accountId) {
            $this->load($accountId);
        }
        return $this;
    }
}
