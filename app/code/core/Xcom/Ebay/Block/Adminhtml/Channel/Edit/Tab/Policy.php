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

class Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tab_Policy
    extends Mage_Adminhtml_Block_Widget_Container
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /** @var Varien_Data_Form */
    protected $_form;

     /**
     * Prepare buttons.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_addButton('cancel_policy_button', array(
            'label'     => $this->__('Cancel'),
            'id'        => 'cancel_policy_button',
            'onclick' => "varPolicyContainer.hideForm();",
            'class'     => 'delete ' . $this->_getAdditionalClass(),
        ), 0);

        $this->_addButton('save_policy_button', array(
            'label'     => $this->__('Save Policy'),
            'id'        => 'save_policy_button',
            'onclick'   => 'policyEditForm.customSubmit();',
            'class'     => 'save ' . $this->_getAdditionalClass(),
        ), 1);

        $this->_addButton('add_policy_button', array(
            'label'     => $this->__('Add Policy'),
            'id'        => 'add_policy_button',
            'onclick'   => 'varPolicyContainer.showForm();',
            'class'     => 'add ' . ($this->_getPolicy()->getEditFlag() ? 'accordion_disabled' : '')
        ), 1);
    }

    protected function _getAdditionalClass()
    {
        return !$this->_getPolicy()->getEditFlag() ? 'accordion_disabled' : '';
    }

    protected function _getPolicy()
    {
        return Mage::registry('current_policy');
    }

    /**
     * @return string
     */
    public function getFormHtml()
    {
        return $this->getForm()->toHtml();
    }

    /**
     * @return Varien_Data_Form
     */
    public function getForm()
    {
        if (null === $this->_form) {
            $form = new Varien_Data_Form(array(
                'id'        => 'policy_edit_form',
                'action'    => $this->getUrl('*/*/savePolicy'),
                'method'    => 'post',
                'enctype'   => 'multipart/form-data'
            ));
            $form->setUseContainer(true);

            //set field with chanel id if channel was already created
            if ($this->getChannel()->getId()) {
                $form->addField('channel_id', 'hidden', array('name' => 'channel_id'));
            }

            $fieldset = $form->addFieldset('accordion_fieldset', array('class' => $this->_getAdditionalClass()));
            $fieldset->addField('accordion', 'note', array(
                'text'  => $this->getChildHtml('policy_accordion')
            ));

            $form->setValues($this->getChannel()->getData());
            $this->_form = $form;
        }
        return $this->_form;
    }

    public function getChannel()
    {
        return Mage::registry('current_channel');
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Policy Settings');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Policy Settings');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
