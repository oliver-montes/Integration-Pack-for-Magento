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
 * @package     Xcom_Log
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Log_Block_Adminhtml_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_log';
        $this->_blockGroup = 'xcom_log';
        $this->_headerText = $this->__('Synchronization Log');
        parent::__construct();
        $this->removeButton('add');
        $message = $this->__('This will clear all the logs. Do you want to continue?');
        $this->addButton('clear', array(
            'label'     => $this->__('Clear Log'),
            'onclick'   => 'confirmSetLocation(\''.$message.'\', \'' . $this->getClearUrl() .'\')',
            'class'     => 'delete',
        ));
    }

    /**
     * Get url for clean log storage
     *
     * @return string
     */
    public function getClearUrl()
    {
        return $this->getUrl('*/*/clear');
    }
}