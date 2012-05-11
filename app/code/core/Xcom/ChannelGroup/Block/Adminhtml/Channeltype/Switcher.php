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
class Xcom_ChannelGroup_Block_Adminhtml_Channeltype_Switcher extends Mage_Adminhtml_Block_Template
{
    /**
     * Constructor. Set default template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('xcom/channelgroup/channeltype/switcher.phtml');
    }

    /**
     * Get channel set
     *
     * @return array
     */
    public function getChannelTypes()
    {
        return Mage::getModel('xcom_channelgroup/config_channeltype')
                ->getAllChannelTypes();
    }

    /**
     * @return Varien_Object
     */
    public function getChannelType()
    {
        return Mage::registry('current_channeltype');
    }

    /**
     * Get base url for channel switcher.
     *
     * @return string
     */
    public function getSwitchUrl()
    {
        $urlParams = array();
        $storeId = (int)$this->getRequest()->getParam('store');
        if ($storeId) {
            $urlParams['store'] = $storeId;
        }
        return $this->getUrl('*/*/index', $urlParams);
    }

    /**
     * @param Varien_Object $channelType
     * @return bool
     */
    public function isCurrentChannelType(Varien_Object $channelType)
    {
        return $this->getChannelType()->getCode() == $channelType->getCode();
    }
}
