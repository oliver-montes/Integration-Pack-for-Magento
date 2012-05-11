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
 * @package     Xcom_ChannelGroup
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_ChannelGroup_Block_Adminhtml_Channeltype_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize class.
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('xcom/channelgroup/tabs.phtml');
        $this->setTitle($this->__('Channels'));
    }

    /**
     * Retrieve currently edited channel type object
     *
     * @return Xcom_ChannelGroup_Model_Channeltype
     */
    public function getChannelType()
    {
        return Mage::registry('current_channeltype');
    }

    /**
     * Prepare layout for tabs
     *
     * @return Xcom_ChannelGroup_Block_Adminhtml_Channeltype_Tabs
     */
    protected function _prepareLayout()
    {
        $model = Mage::getModel('xcom_channelgroup/config_channeltype');
        foreach ($model->getAllTabs() as $item) {
            switch ($item->getType()) {
                case Xcom_ChannelGroup_Model_Config_Channeltype::GROUP_ID:
                    $this->addTab("xcom_channelgroup_tab_{$item->getCode()}", array(
                        'label'     => $item->getTitle(),
                        'type'      => $item->getType(),
                    ));
                    break;

                case Xcom_ChannelGroup_Model_Config_Channeltype::TYPE_ID:
                    $this->addTab("xcom_channeltype_tab_{$item->getCode()}", array(
                        'label'     => $item->getTitle(),
                        'url'       => $this->getUrl("*/*/*", array('type' => "{$item->getCode()}")),
                        'active'    => ($item->getCode() == $this->getChannelType()->getCode()) ? true : false,
                        'type'      => $item->getType(),
                    ));
                    break;
            }
        }
        return parent::_prepareLayout();
    }

    /**
     * Check the tab item type
     * Return TRUE if it is Channel Group object and FALSE otherwise.
     *
     * @param $tab
     * @return bool
     */
    public function isChannelGroup($tab)
    {
        if ($tab instanceof Varien_Object) {
            if ($tab->getType() == Xcom_ChannelGroup_Model_Config_Channeltype::GROUP_ID) {
                return true;
            }
        }
        return false;
    }
}
