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

class Xcom_Google_Block_Adminhtml_Account extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Initialize class.
     */
    public function _construct()
    {
        $this->_headerText = $this->__('Authorized Accounts');
        $this->_controller = 'adminhtml_google_account';
 
        $size = Mage::getModel('xcom_cse/account')->getCollection()
            ->addChanneltypeCodeFilter(Mage::helper('xcom_google')->getChanneltypeCode())
            ->getSize();
		
        $this->_addButton('add', array(
            'label'     => $this->__('Add Account'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/google_account/new') .'\')',
        	// Only allow one account at this time.
			'disabled'  => ($size == 0 ? false : true),
			'class'		=> ($size == 0 ? 'add' : 'disabled')
        
        ));
        
        parent::_construct();
    }

    /**
     * Get grid child block.
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
    
    /**
     * Get InitAuthorizationMessage url.
     *
     * @return string
     */
    public function getInitAuthorizationMessageUrl()
    {
        return $this->getUrl('*/google_account/init');
    }
}
