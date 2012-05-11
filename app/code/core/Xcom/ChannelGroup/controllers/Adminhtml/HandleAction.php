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

require_once 'Xcom/ChannelGroup/controllers/Adminhtml/Action.php';

class Xcom_ChannelGroup_Adminhtml_HandleAction extends Xcom_ChannelGroup_Adminhtml_Action
{
    protected $_handleName;

    /**
     * Load custom layout.
     * <handleName>_<channelTypeCode>
     *
     * @return Xcom_ChannelGroup_Adminhtml_HandleAction
     */
    protected function _initLayout()
    {
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();
        if ($this->_handleName) {
            $update->addHandle($this->_handleName . '_' . $this->getChannelType()->getCode());
        }
        $this->loadLayoutUpdates();

        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_initLayoutMessages('adminhtml/session');
        return $this;
    }
}
