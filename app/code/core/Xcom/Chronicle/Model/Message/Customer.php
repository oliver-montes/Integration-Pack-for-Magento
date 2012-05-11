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
 * @package     Xcom_Chronicle
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Chronicle_Model_Message_Customer extends Varien_Object
{
    /**
     * @param Mage_Customer_Model_Customer $customer
     */
    public function __construct(Mage_Sales_Model_Order_Shipment $customer)
    {
        $this->setData($this->_createCustomer($customer));
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     */
    protected function _createCustomer(Mage_Customer_Model_Customer $customer)
    {
        $data = array(
            'customerId'    => $customer->getEntityId(),
            //??
            'sourceCustomerId'   => null,
            //??
            'lastActivityDate'   => null,
            'primaryContact'     => $this->_createContact($customer, true),
            'additionalContacts' => $this->_createContact($customer, false)
        );

        return $data;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $isPrimary
     * @return array|null
     */
    protected function _createContact(Mage_Customer_Model_Customer $customer, boolean $isPrimary)
    {
        $result = null;

        if ($isPrimary) {
            $result = array(
                'fullName'          => $this->_createName($customer),
                'address'           => null,
                'primaryPhone'      => null,
                'additionalPhone'   => null,
                'emailAddress'      => $this->_createEmailAddress($customer),
                'extension'         => null
            );
        }
        else {
            $addresses = $customer->getAddresses();
            if (!empty($addresses)) {
                $result = array();
                foreach ($addresses as $address) {
                    $data = array(
                       'fullName'           => $this->_createName($address),
                       'address'            => $this->_createAddress($address),
                       'primaryPhone'       => $this->_createPhoneNumber($address),
                       'additionalPhone'    => null,
                       'emailAddress'       => null,
                       'extension'          => null
                    );
                    $result[] = $data;
                }
            }
        }

        return $result;
    }

    /**
     * @param $name
     * @return array
     */
    protected function _createName($name)
    {
        $data = array(
            'firstName'     => $name->getFirstname(),
            'middleName'    => strlen($name->getMiddlename()) > 0 ? $name->getMiddlename() : null,
            'lastName'      => $name->getLastname(),
            'prefix'        => strlen($name->getPrefix()) > 0 ? $name->getPrefix() : null,
            'suffix'        => strlen($name->getSuffix()) > 0 ? $name->getSuffix() : null
        );

        return $data;
    }

   /**
    * @param Mage_Customer_Model_Address $address
    * @return array|null
    */
    protected function _createAddress(Mage_Customer_Model_Address $address)
    {
        $region = $address->getRegion();
        $data = array(
            'street1'           => $address->getStreet1(),
            'street2'           => strlen($address->getStreet2()) > 0 ? $address->getStreet2() : null,
            'street3'           => strlen($address->getStreet3()) > 0 ? $address->getStreet3() : null,
            'street4'           => strlen($address->getStreet4()) > 0 ? $address->getStreet4() : null,
            'city'              => $address->getCity(),
            'county'            => null,
            'stateOrProvince'   => empty($region) ? null : $region,
            'postalCode'        => $address->getPostcode(),
            'country'           => Mage::getModel('directory/country')
                ->loadByCode($address->getCountryId())
                ->getIso3Code()
        );

        return $data;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return array
     */
    protected function _createEmailAddress(Mage_Customer_Model_Customer $customer)
    {
        $data = array(
            'emailAddress'  => $customer->getEmail(),
            'extension'     => null
        );

        return $data;
    }

    /**
     * @param Mage_Customer_Model_Address $address
     * @return array|null
     */
    protected function _createPhoneNumber(Mage_Customer_Model_Address $address)
    {
        $primaryPhone = $address->getTelephone();

        $data = array(
            'number'    => $primaryPhone,
            //?? type
            'type'      => 'HOME'
        );

        return $data;
    }
}