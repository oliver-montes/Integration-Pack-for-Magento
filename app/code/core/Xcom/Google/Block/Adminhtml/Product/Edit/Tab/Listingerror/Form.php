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
class Xcom_Google_Block_Adminhtml_Product_Edit_Tab_Listingerror_Form extends Mage_Adminhtml_Block_Widget_Form
{
   /**
     * Init Form properties
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('xcom_google_product_tab_listingerror_form');
    }

    /**
     * Prepare product collection
     *
     * @return Xcom_Cse_Model_Resource_Product_Collection
     */
    protected function _prepareCollection()
    {
        $logResponseId = $this->getRequest()->getParam('response');

        $logRequest = Mage::getModel('xcom_listing/message_listing_log_request')
            ->getCollection()
            ->addResponseIdFilter($logResponseId)
            ->getFirstItem();

        $collection = new Varien_Data_Collection();
        $collection->addItem(new Varien_Object(array('request_body' => $logRequest->getRequestBody())));
        return $collection;
    }

    /**
     * Prepare form fields
     */
    protected function _prepareForm()
    {
        $collection = $this->_prepareCollection();
        $request    = $collection->getLastItem();
        $form = new Varien_Data_Form(array(
            'id'        => $this->getId(),
            'method' => 'post'
        ));
        $fieldset = $form->addFieldset('request_fieldset', array());
        $fieldset->addField('request_body', 'textarea', array(
            'name'      => 'request_body',
            'label'     => $this->__('Request'),
            'title'     => $this->__('Request'),
            'value'     => $request->getRequestBody(),
            'readonly'  => 'readonly',
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
