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
 * @package     Xcom_CseOffer
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_CseOffer_Helper_Validator extends Mage_Core_Helper_Abstract
{
    /**
     * @var Xcom_CseOffer_Model_CseOffer
     */
    protected $_cseoffer;

    /**
     * @param Xcom_CseOffer_Model_CseOffer $cseoffer
     * @return Xcom_CseOffer_Helper_Validator
     */
    public function setCseOffer(Xcom_CseOffer_Model_CseOffer $cseOffer)
    {
        $this->_cseOffer = $cseOffer;
        return $this;
    }

    /**
     * @return Xcom_CseOffer_Helper_Validator
     * @throws Mage_Core_Exception
     */
    public function validateProducts()
    {
        $messages = array();
        foreach ($this->_cseOffer->getProducts() as $product) {
            $sku = $product->getSku();
            if (!$this->_isProductEnabled($product)) {
                $messages[] = $this->__('Requested product "%s" is unavailable.', $sku);
            }
            if (!$product->getIsInStock()) {
                $messages[] = $this->__('Requested product "%s" is out of stock.', $sku);
            }
            if (!$this->_isProductImageSpecified($product)) {
                $messages[] = $this->__('Requested product "%s" does not have an image specified.', $sku);
            }
        }

        $this->_checkAndThrowException($messages);

        return $this;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    protected function _isProductEnabled(Mage_Catalog_Model_Product $product)
    {
        return $product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    protected function _isProductImageSpecified(Mage_Catalog_Model_Product $product)
    {
        $image = $product->getData('image');
        return ($image && $image !='no_selection');
    }
        
    /**
     * @param array $messages
     * @throws Mage_Core_Exception
     */
    protected function _checkAndThrowException(array $messages)
    {
        if ($messages) {
            throw Mage::exception('Mage_Core', implode('<br />', $messages));
        }
    }
}
