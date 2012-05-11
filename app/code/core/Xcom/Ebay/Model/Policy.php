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
 * @package     Xcom_Ebay
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Ebay_Model_Policy extends Xcom_Mmp_Model_Policy
{
    /**
     * Class constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('xcom_ebay/policy');
        $this->setChanneltypeCode(Mage::helper('xcom_ebay')->getChanneltypeCode());
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad()
    {
        if ($this->getPaymentName()) {
            $this->setPaymentName(explode(",", $this->getPaymentName()));
        }
        return parent::_afterLoad();
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if (is_array($this->getPaymentName())) {
            $this->setPaymentName(implode(",", $this->getPaymentName()));
        }
        if (!$this->hasPaymentPaypalEmail()) {
            $this->setPaymentPaypalEmail(null);
        }

        return parent::_beforeSave();
    }

    /**
     * prepare shipping data for policy
     * array($shippingId => array(
     *      'cost' => $shippingCost,
     *      'sortOrder' => $shippingOrder
     * ))
     *
     * @return Xcom_Ebay_Model_Policy
     */
    public function prepareShippingData()
    {
        $data               = array();
        $shippingNames      = $this->getShippingName() ? $this->getShippingName() : array();
        $shippingCosts      = $this->getShippingCost();
        $order = 1;
        foreach ($shippingNames as $shippingId) {
            $cost   = null;

            if ($shippingId =='' || $shippingId === null) {
                continue;
            }

            if (isset($shippingCosts[$shippingId])) {
                $cost   = round(min($shippingCosts[$shippingId], 99999999.9999),2);
            }
            $data[$shippingId]  = array(
                'cost'          => $cost,
                'sort_order'    => $order++,
                'shipping_id'   => $shippingId
            );
        }
        $this->setShippingData($data);
        return $this;
    }

    /**
     * @return Xcom_Ebay_Model_Policy
     */
    public function validate()
    {
        if (!$this->isPolicyNameUnique($this->getName(), $this->getChannelId(), $this->getId())) {
            $this->_throwException('Policy with name "%s" is already exist.');
        }

        if (!$this->getPaymentName()) {
           $this->_throwException('Payment method for policy "%s" is required.');
        }

        if (!$this->getShippingData()) {
            $this->_throwException('Shipping method for policy "%s" is required.');
        }

        if (!$this->getLocation()) {
            $this->_throwException('Product location for policy "%s" is required.');
        }

        if (!$this->getPostalCode()) {
            $this->_throwException('Postal code for policy "%s" is required.');
        }

        //value can be 0
        if (!$this->hasHandlingTime() || $this->getHandlingTime() === '') {
            $this->_throwException('Handling Time for policy "%s" is required.');
        }

        return $this;
    }

    /**
     * @param string $message
     * @throws Mage_Core_Exception
     */
    protected function _throwException($message)
    {
        throw Mage::exception('Mage_Core',
            Mage::helper('xcom_ebay')->__($message, Mage::helper('core')->escapeHtml($this->getName())));
    }
}
