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

class Xcom_Ebay_Block_Adminhtml_Account_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $account = $this->getAccount();
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('settings', array(
            'legend' => $this->__('Authorization')
        ));

        $fieldset->addField('auth_id',  'hidden', array(
            'name'      => 'auth_id',
        ));

        $fieldset->addField('xaccount_id',  'hidden', array(
            'name'      => 'xaccount_id',
        ));

        $fieldset->addField('user_id',  'hidden', array(
            'name'      => 'user_id',
        ));

        $fieldset->addField('validated_at',  'hidden', array(
            'name'      => 'validated_at',
        ));

        $environmentOptions = array('' => 'Please Select One');
        $environmentOptions += Mage::helper('xcom_ebay')->getEnvironmentHash();

        $fieldset->addField('environment', 'select', array(
            'name'      => 'environment',
            'label'     => $this->__('Environment'),
            'class'     => 'input-select required-entry',
            'style'     => 'width: 140px',
            'options'   => $environmentOptions,
            'required'  => true,
            'disabled'  => ($account->getId() ? true : false),
        ));

        $form->setValues($account->getData());

        $fieldset->addField('user_id_text', 'note', array(
            'name'          => 'user_id_text',
            'container_id'  => 'user_id_text_container',
            'label'         => $this->__('User ID'),
            'text'          => '<strong>' . $account->getUserId() . '</strong>',
            'note'          => $this->getValidatedAtText(),
        ));

        $fieldset->addField('error_message', 'note', array(
            'name'          => 'error_message',
            'container_id'  => 'error_message_container',
            'text'          => $this->__('Authorization not complete'),
        ));

        $fieldset->addField('authorize_button', 'button',
            array(
                'name'      => 'authorize_button',
                'value'     => $this->__('Authorize eBay Account'),
                'class'     => 'form-button',
                'note'      => $this->__('Note: You will be redirected to eBay to authenticate your merchant account'),
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Get InitAuthorizationMessage url.
     *
     * @return string
     */
    public function getInitAuthorizationMessageUrl()
    {
        return $this->getUrl('*/ebay_account/init');
    }

    /**
     * Get CompleteAuthorizationMessage url.
     *
     * @return string
     */
    public function getCompleteAuthorizationMessageUrl()
    {
        return $this->getUrl('*/ebay_account/complete');
    }

    public function getAccount()
    {
        return Mage::registry('current_account');
    }

    public function hasUserId()
    {
        return $this->getAccount()->hasUserId();
    }

    public function getValidatedAtText()
    {
        $account = $this->getAccount();
        if ($account->getValidationExpired()) {
            $message = $this->__('Expired');
            return sprintf('<span class="error">%s</span>', $message);
        }
        return $this->__('Valid Authorization Token.<br/>' .
                         'Valid until %s', $account->getValidatedAt());
    }
}
