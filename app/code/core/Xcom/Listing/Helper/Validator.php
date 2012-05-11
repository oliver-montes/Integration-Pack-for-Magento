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
 * @package     Xcom_Listing
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Listing_Helper_Validator extends Mage_Core_Helper_Abstract
{
    /**
     * @var Xcom_Listing_Model_Listing
     */
    protected $_listing;

    protected $_fieldGroups = array(
        'price' => array('price_type', 'price_value', 'price_value_type'),
        'qty'   => array('qty_value_type', 'qty_value'),
        'policy' => array('policy_id'),
        'category' => array('category_id'),
    );

    /**
     * @param Xcom_Listing_Model_Listing $listing
     * @return Xcom_Listing_Helper_Validator
     */
    public function setListing(Xcom_Listing_Model_Listing $listing)
    {
        $this->_listing = $listing;
        return $this;
    }

    /**
     * Validate all required fields for channels.
     *
     * @throws Mage_Core_Exception
     * @return Xcom_Listing_Helper_Validator
     */
    public function validateFields()
    {
        $messages = array();
        foreach ($this->_getValidationRules() as $rule) {
            foreach ($rule['fields'] as $field) {
                if ($this->_isFieldEmpty($field)) {
                    if ($field == 'price_value'
                        && ($this->_listing->isMagentoPriceType($this->_listing->getData('price_type')))) {
                        continue;
                    }
                    $messages[] = $this->__($rule['message']);
                }
            }
        }

        $this->_checkAndThrowException($messages);

        return $this;
    }

    /**
     * @return array
     */
    protected function _getValidationRules()
    {
        return array(
            array(
                'fields'    => array('category_id'),
                'message'   => 'Category is required field.',
            ),
            array(
                'fields'    => array('price_type', 'price_value'),
                'message'   => 'Price is required field.',
            ),
            array(
                'fields'    => array('qty_value_type', 'qty_value'),
                'message'   => 'Quantity is required field.',
            ),
            array(
                'fields'    => array('policy_id'),
                'message'   => 'Policy is required field.',
            ),
        );
    }

    /**
     * @param string $field
     * @return bool
     */
    protected function _isFieldEmpty($field)
    {
        return !$this->_listing->hasData($field)
            || !Zend_Validate::is($this->_listing->getData($field), 'NotEmpty', array(Zend_Validate_NotEmpty::ALL));
    }

    /**
     * Validation rules:
     *  - policy_id parameter should has value.
     *  - category_id parameter should has value.
     *  - all fields for price should be filled.
     *  - all fields for qty should be filled.
     *
     * @return Xcom_Listing_Helper_Validator
     * @throws Mage_Core_Exception
     */
    public function validateOptionalFields()
    {
        $messages = array();
        if ($this->_isValueNotExist('policy_id')) {
            $messages[] = $this->__('You have changed policy to void value. Please, specify valid policy.');
        }
        if ($this->_isValueNotExist('category_id')) {
            $messages[] = $this->__('You have changed category to void value. Please, specify valid category.');
        }

        if (!$this->_validatePriceCombination()) {
            $messages[] = $this->__('You have changed price. Please provide all price related information.');
        }

        if (!$this->_validateQtyCombination()) {
            $messages[] = $this->__('You have changed quantity. Please provide all quantity related information.');
        }

        $this->_checkAndThrowException($messages);

        return $this;
    }

    /**
     * @param string $fieldName
     * @return bool
     */
    protected function _isValueNotExist($fieldName)
    {
        if ($this->_listing->hasData($fieldName) && !(int)$this->_listing->getData($fieldName)) {
            return true;
        }
        return false;
    }

    /**
     * Validate that all combinations of price fields are complete.
     * @return bool
     */
    protected function _validatePriceCombination()
    {
        $allFieldsFilled = $this->_getChangedFieldsCount('price');
        if ($allFieldsFilled && $allFieldsFilled != count($this->_fieldGroups['price'])
            && !$this->_listing->isMagentoPriceType($this->_listing->getData('price_type'))) {
                return false;
        }
        return true;
    }

    /**
     * Validate that all combinations of qty fields are complete.
     * @return bool
     */
    protected function _validateQtyCombination()
    {
        $allFieldsFilled = $this->_getChangedFieldsCount('qty');
        if ($allFieldsFilled && $allFieldsFilled != count($this->_fieldGroups['qty'])) {
            return false;
        }
        return true;
    }

    /**
     * @param string $groupName
     * @return int
     */
    protected function _getChangedFieldsCount($groupName)
    {
        $changedFields = array();
        foreach ($this->_fieldGroups[$groupName] as $fieldName) {
            $changedFields[$fieldName] = (int)!$this->_isFieldEmpty($fieldName);
        }
        return array_sum($changedFields);
    }

    /**
     * @return bool
     */
    public function isPriceChanged()
    {
        return $this->_getChangedFieldsCount('price') > 0;
    }

    /**
     * @return bool
     */
    public function isQtyChanged()
    {
        return $this->_getChangedFieldsCount('qty') > 0;
    }

    /**
     * @param bool $isQtyChanged
     * @param bool $isPriceChanged
     * @return Xcom_Listing_Helper_Validator
     * @throws Mage_Core_Exception
     */
    public function validateProducts($isQtyChanged = true, $isPriceChanged = true)
    {
        $messages = array();
        foreach ($this->_listing->getProducts() as $product) {
            $sku = $product->getSku();
            if (!$this->_isProductEnabled($product)) {
                $messages[] = $this->__('Requested product "%s" is unavailable.', $sku);
            }
            if (!$product->getIsInStock()) {
                $messages[] = $this->__('Requested product "%s" is out of stock.', $sku);
            }
            if ($isQtyChanged && $this->_isProductQtyUnavailable($product)) {
                $messages[] = $this->__('Requested quantity for product "%s" is unavailable.', $sku);
            }
            if ($isQtyChanged && $product->getListingQty() <= 0) {
                $messages[] = $this->__('Requested quantity for product "%s" is unacceptable. Quantity should be higher than 0', $sku);
            }
            if ($isPriceChanged && $product->getListingPrice() <= 0) {
                $messages[] = $this->__('Requested price for product "%s" is less then 0.', $sku);
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
    protected function _isProductQtyUnavailable(Mage_Catalog_Model_Product $product)
    {
        $qty = $product->getStockItem()->getQty();
        return ($qty <= 0) || ($qty < $product->getListingQty());
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
