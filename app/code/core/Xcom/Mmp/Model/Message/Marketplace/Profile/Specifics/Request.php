
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

class Xcom_Mmp_Model_Message_Marketplace_Profile_Specifics_Request extends Xcom_Xfabric_Model_Message_Request
{
    protected function _construct()
    {
        parent::_construct();
        $this->_schemaRecordName = 'MarketplaceProfileSpecifics';
        $this->_topic      = 'com.x.marketplace.ebay.v1/MarketplaceProfileSpecifics';
    }

    /**
     * Prepare data before sending
     *
     * @param Varien_Object $dataObject
     *
     * @return Xcom_Xfabric_Model_Message_Abstract
     */
    public function _prepareData(Varien_Object $dataObject = null)
    {
        $data = array(
            'location'              => $dataObject->getData('location') ? $dataObject->getData('location') : null,
            'postalCode'            => $dataObject->getData('postalCode') ? $dataObject->getData('postalCode') : null,
            'countryCode'           => $dataObject->getData('countryCode') ? $dataObject->getData('countryCode') : '',
            'payPalEmailAddress'    => $dataObject->getData('payPalEmailAddress')
                    ? (string)$dataObject->getData('payPalEmailAddress') : null,
            'handlingTime'          => null,
            'useTaxTable'           => $dataObject->getData('useTaxTable') ? $dataObject->getData('useTaxTable') : null
        );
        //send handling time as NULL if it has 'none' value
        if ($dataObject->hasData('handlingTime') && strtolower($dataObject->getData('handlingTime')) != 'none') {
            $data['handlingTime']   = (int)$dataObject->getData('handlingTime');
        }

        $this->setMessageData($data);
        return parent::_prepareData($dataObject);
    }
}
