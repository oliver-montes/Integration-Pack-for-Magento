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

class Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Accordion_Return extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy_Accordion_Return
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        Varien_Data_Form::getFieldsetElementRenderer()
            ->setTemplate('xcom/ebay/widget/form/renderer/fieldset/element.phtml');
        return $this;
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _prepareForm()
    {
        $form           = new Varien_Data_Form();
        $fieldset       = $form->addFieldset('return_fieldset', array());

        $returnData     = $this->getReturnPolicies();
        $showFullData   = Mage::helper('xcom_mmp/channel')
            ->showFullReturnPolicyData(Mage::registry('current_channel')->getSiteCode());

        $returnAcceptedValues   =  array(
            array('value' => 0, 'label'     => $this->__('No')
        ));
        if (!empty($returnData['returns_accepted'])) {
            $returnAcceptedValues[] = array('value'     => 1, 'label'     => $this->__('Yes'));
        }

        $fieldset->addField('return_accepted', 'select', array(
            'label'     => $this->__('Return Accepted'),
            'name'      => 'return_accepted',
            'values'    => $returnAcceptedValues,
            'required'  => true,
        ));

        if (!empty($returnData) && $showFullData) {
            $fieldset->addField('return_by_days', 'select', array(
                'label'                 => $this->__('After receiving the item, your buyer should contact you within'),
                'name'                  => 'return_by_days',
                'values'                => $this->getReturnPeriodValues($returnData['max_return_by_days']),
                'required'              => true,
                'element_html_class'    => 'return_fields'
            ));

            $returnMethodValues = !empty($returnData['methods'])
                    ? $this->getReturnMethodValues($returnData['methods'])
                    : array();
            $fieldset->addField('refund_method', 'select', array(
                'label'                 => $this->__('Refund will be given as'),
                'name'                  => 'refund_method',
                'values'                => $returnMethodValues,
                'required'              => true,
                'element_html_class'    => 'return_fields'
            ));

            $fieldset->addField('shipping_paid_by', 'select', array(
                'label'                 => $this->__('Return shipping will be paid by'),
                'name'                  => 'shipping_paid_by',
                'values'                => array(
                    array('value' => 'buyer', 'label' => $this->__('Buyer')),
                    array('value' => 'seller', 'label' => $this->__('Seller'))
                ),
                'required'              => true,
                'element_html_class'    => 'return_fields'
            ));

            $fieldset->addField('return_description', 'textarea', array(
                'label'                 => $this->__('Additional Return Details'),
                'name'                  => 'return_description',
                'element_html_class'    => 'return_fields'
            ));

        }
        if ($this->getPolicy()) {
            $form->setValues($this->getPolicy()->getData());
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Retrieve current policy object
     *
     * @return mixed
     */
    public function getPolicy()
    {
        return Mage::registry('current_policy');
    }

    /**
     * Retrieve return methods to option values format
     *
     * @param array $methods
     * @return array
     */
    public function getReturnMethodValues($methods)
    {
        $data = array();
        foreach ($methods as $methodName) {
            $data[] = array('value' => $methodName, 'label' => $methodName);
        }
        return $data;
    }

    /**
     * Retrieve list of days for return.
     *
     * @param int $maxPeriod - max days of return
     * @return array
     */
    public function getReturnPeriodValues($maxPeriod)
    {
        //TODO:: need to clarify the list of this data
        $data       = array();
        //list of standard numbers for return days
        $periods    = $this->getPolicy()->getReturnedPeriods();
        if ($maxPeriod < current($periods)) {
            $data[] = $this->_getReturnPeriodOptionData($maxPeriod);
        } else {
            foreach ($periods as $periodDay) {
                if ($periodDay > $maxPeriod) {
                    break;
                }
                $data[] = $this->_getReturnPeriodOptionData($periodDay);
            }
            if ($maxPeriod > end($periods)) {
                $data[] = $this->_getReturnPeriodOptionData($maxPeriod);
            }
        }
        return $data;
    }

    public function getReturnPolicies()
    {
        if (Mage::registry('current_channel')) {
            return Mage::getResourceModel('xcom_mmp/returnPolicy')
                ->getReturnPolicies(Mage::registry('current_channel'));
        }
        return null;
    }

    /**
     * Pattern for period option data
     *
     * @param string $value
     * @return array
     */
    protected function _getReturnPeriodOptionData($value)
    {
        return array('value' => $value, 'label' => $this->__('%s Days', $value));
    }
}
