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

/**
 * Class of admin edit channel tab block
 *
 * @category   Mage
 * @package    Xcom_Google
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Xcom_Google_Block_Adminhtml_Channel_Edit_Tab_Channel extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form before rendering HTML.
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $channelId = $this->getChannel()->getId();

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('channel_id' => $channelId)),
            'method'    => 'post',
            'enctype'   => 'multipart/form-data'
        ));
        $form->setUseContainer(true);

        $fieldset   = $form->addFieldset(
            'xcom_cse_form',
            array('legend' => $this->__('General Settings'))
        );

        $fieldset->addField('channeltype_code',  'hidden', array(
            'value'     => $this->getChannel()->getChanneltypeCode(),
            'name'      => 'channeltype_code',
        ));

        $fieldset->addField('store_id', 'select', array(
            'name'      => 'store_id',
            'label'     => $this->__('Store View'),
            'title'     => $this->__('Store View'),
            'required'  => true,
            'disabled'  => ($channelId ? true : false),
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(true, false),
        ));

        $fieldset->addField('site_code', 'select', array(
            'label'     => $this->__('Target Country'),
            'name'      => 'site_code',
            'required'  => true,
            //no possibility to change if channel is already saved
            'disabled'  => ($channelId ? true : false),
            'values'    => $this->_getAllSites()
        ));

        $fieldset->addField('account_id', 'select', array(
            'label'     => $this->__('Google Account'),
            'name'      => 'account_id',
            'required'  => true,
            //no possibility to change if channel is already saved
            'disabled'  => ($channelId ? true : false),
            'values'    => $this->_getEnabledAndValidAccounts()
        ));

        $fieldset->addField('name', 'text', array(
            'label'     => $this->__('Name'),
            'required'  => true,
            'name'      => 'name',
        ));

        $fieldset->addField('offer_name', 'text', array(
            'label'     => $this->__('Feed Filename'),
            'name'      => 'offer_name',
            'required'  => true,        
        ));
        
        $fieldset->addField('is_active', 'select', array(
            'label'     => $this->__('Status'),
            'name'      => 'is_active',
            'values'    => array(
                array(
                    'value'     => 0,
                    'label'     => $this->__('Disabled'),
                ),
                array(
                    'value'     => 1,
                    'label'     => $this->__('Enabled'),
                ),
            ),
        ));

        if ($this->getChannel()) {
            $form->setValues($this->getChannel()->getData());
            if (null === $this->getChannel()->getIsActive()) {
                $form->getElement('is_active')->setValue(1);
            }
        }

        if (Mage::getSingleton('adminhtml/session')->getXcomGoogleData()){
            $form->setValues(Mage::getSingleton('adminhtml/session')->getXcomGoogleData());
            Mage::getSingleton('adminhtml/session')->setXcomGoogleData(null);
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Returns channel instance.
     *
     * @return Xcom_Cse_Model_Channel
     */
    public function getChannel()
    {
        return Mage::registry('current_channel');
    }

    /**
     * Retrieve site options for current channel
     *
     * @return array
     */
    protected function _getAllSites()
    {
        return Mage::getSingleton('xcom_google/source_site')->toOptionArray();
    }

    /**
     * Retrieve enabled and valid Google sites array.
     *
     * @return array
     */
    protected function _getEnabledAndValidAccounts()
    {
        /** @var $source Xcom_Google_Model_Source_Account */
        $source = Mage::getSingleton('xcom_google/source_account')->setChannel($this->getChannel());
        return $source->toOptionArray();
    }
}
