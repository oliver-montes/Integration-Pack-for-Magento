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
 * @package     Xcom_ChannelOrder
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_ChannelOrder_Block_Adminhtml_Sales_Order_Creditmemo_Create_Items
    extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create_Items
{
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $message = $this->jsQuoteEscape(
            $this->__('This will create Credit memo in Magento. This request is not sent to channel.')
        );
        if ($this->getCreditmemo()->canRefund()) {
            $this->getChild('submit_offline')->setData('onclick',"confirmSetLocationCreditMemo('$message', false);");
            if ($this->getChild('submit_button')) {
                $this->getChild('submit_button')->setData('onclick', "confirmSetLocationCreditMemo('$message', true);");
            }
        } else {
            $this->getChild('submit_button')->setData('onclick', "confirmSetLocationCreditMemo('$message', false);");
        }
        return $this;
    }
}
