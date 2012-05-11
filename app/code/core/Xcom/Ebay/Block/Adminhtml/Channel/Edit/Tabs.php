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

class Xcom_Ebay_Block_Adminhtml_Channel_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('xcom/ebay/channel/tabs.phtml');
        $this->setId('channel_info_tabs');
        $this->setDestElementId('channel_tab_content');

        $channelId = $this->getRequest()->getParam('channel_id', null);
        if ($channelId) {
            $this->setTitle($this->__('Edit Channel'));
        } else {
            $this->setTitle($this->__('New Channel'));
        }
    }

    /**
     * Add tabs.
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->addTab('channel', array(
            'label'     => $this->__('General Settings'),
            'content'   => $this->getLayout()
                    ->createBlock('xcom_ebay/adminhtml_channel_edit_tab_channel') //form
                    ->toHtml()
        ));
        $this->addTab('policy', array(
            'label' => $this->__('Policy Settings'),
            'class' => 'ajax',
            'url'   => $this->_getTabPolicyUrl() //added to prevent bug with Prototype's event stop in IE9
        ));
        return parent::_prepareLayout();
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
     * Check if channel saved and has id
     *
     * @param $tab
     * @return bool
     */
    public function getTabIsDisabled($tab)
    {
        if ($tab->getTabId() == 'policy') {
            if (!(int)$this->getChannel()->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve tab policy url if tab is not disabled and a dash otherwise
     *
     * @return string
     */
    protected function _getTabPolicyUrl()
    {
        if (!(int)$this->getChannel()->getId()) {
            return '#';
        }
        return $this->getUrl('*/*/policy', array('_current' => true));
    }
}
