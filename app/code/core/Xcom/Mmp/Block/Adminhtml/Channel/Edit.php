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

/**
 * Core Channel's Form Container
 *
 * @category    Xcom
 * @package     Xcom_Mmp
 */
class Xcom_Mmp_Block_Adminhtml_Channel_Edit extends Mage_Adminhtml_Block_Widget_Container
{
    protected function _construct()
    {
        $this->_controller  = 'adminhtml_channel';
        parent::_construct();
        $this->_addButton('back', array(
            'label'     => $this->__('Back'),
            'onclick'   => 'setLocation(\'' . $this->getBackUrl() . '\')',
            'class'     => 'back',
        ), -1);
        $this->_addButton('reset', array(
            'label'     => $this->__('Reset'),
            'onclick'   => 'setLocation(window.location.href)',
        ), -1);

        $this->_addButton('save', array(
            'label'     => $this->__('Save'),
            'onclick'   => 'editForm.submit();',
            'class'     => 'save',
        ), 1);

        $this->_addButton('saveandcontinue', array(
            'label'     => $this->__('Save And Continue Edit'),
            'onclick'   => 'editForm.submit($(\'edit_form\').action+\'back/edit/\')',
            'class'     => 'save',
        ), -100);
    }

    /**
     * Get text for form's header
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getChannel()->getId()) {
            return $this->__('Edit "%s" Channel', $this->escapeHtml($this->getChannel()->getName()));
        }
        return $this->__('New "%s" Channel', $this->escapeHtml($this->getChannelType()->getTitle()));
    }

    /**
     * Returns core channel object
     *
     * @return Xcom_Mmp_Model_Channel
     */
    public function getChannel()
    {
        return Mage::registry('current_channel');
    }

    /**
     * @return Varien_Object
     */
    public function getChannelType()
    {
        return Mage::getModel('xcom_channelgroup/config_channeltype')
            ->getChanneltype($this->getChannel()->getChanneltypeCode());
    }

    /**
     * Get header width
     *
     * @return string
     */
    public function getHeaderWidth()
    {
        return 'width: 70%;';
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/channel/', array(
                'type' => $this->getChannel()->getChanneltypeCode())
        );
    }
}
