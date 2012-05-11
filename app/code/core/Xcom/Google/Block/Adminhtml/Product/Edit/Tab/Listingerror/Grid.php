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
class Xcom_Google_Block_Adminhtml_Product_Edit_Tab_Listingerror_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize class.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('google_channel_product_listingerror_grid');
        $this->setSaveParametersInSession(true);
        $this->setEmptyText($this->__('There is no data associated with this listing action.'));
        $this->setPagerVisibility(true);
        $this->setFilterVisibility(false);
    }

    /**
     * Prepare collection.
     *
     * @return Xcom_Google_Model_Resource_Channel_Listingerror_Collection
     */
    protected function _prepareCollection()
    {
        $responseId = (int)$this->getRequest()->getParam('response', 0);
        $productId = (int)$this->getRequest()->getParam('id', 0);
        if ($productId && $responseId) {
            /* @var $listingLogResponse Xcom_Cse_Model_Message_Listing_Log_Response */
            $listingLogResponse = Mage::getModel('xcom_listing/message_listing_log_response')->load((int)$responseId);
            $collection = $this->_filterErrorResponse($productId,
                json_decode($listingLogResponse->getResponseBody(), true));
            $this->setCollection($collection);
        }
        return parent::_prepareCollection();
    }


    /**
     * Looks through the response body for the relavent errors for the product being viewed
     *
     * @param int $productId
     * @param array $responseBody
     * @return Varien_Data_Collection
     */
    protected function _filterErrorResponse($productId, array $responseBody)
    {
        $errorCollection = new Varien_Data_Collection();
        $sku = Mage::getModel('catalog/product')->load((int)$productId)->getSku();

        //Look for different array configurations which change depending on topic (/listing/cancel returns SKU only)
        foreach ($responseBody['errors'] as $errors) {
            //for listing/createFailed message
            if ((isset($errors['listing']['product']['sku']) && $errors['listing']['product']['sku'] == $sku)) {
                $this->_addItemsToCollection($errorCollection, $errors['errors']);
                break;
            } elseif ((isset($errors['sku']) && $errors['sku'] == $sku)) {
                //for listing/cancelFailed message
                $this->_addItemsToCollection($errorCollection, $errors['errors']);
            }
        }

        return $errorCollection;
    }

    /**
     * @param Varien_Data_Collection $collection
     * @param array $items
     * @return Xcom_Google_Block_Adminhtml_Product_Edit_Tab_Listingerror_Grid
     */
    protected function _addItemsToCollection($collection, array $items)
    {
        // We support only Error type, no one else
        foreach($items as $item) {
            $item['responce_type'] = $this->__('Error');
            $collection->addItem(new Varien_Object($item));
        }
        return $this;
    }

    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('responce_type', array(
                'header'    => $this->__('Response Type'),
                'index'     => 'responce_type',
                'type'      => 'text',
                'default'   => '--',
                'sortable'  => true,
        ));
        $this->addColumn('code',
            array(
                'header'  => $this->__('Error Code'),
                'align'   => 'left',
                'index'   => 'code',
                'type'    => 'text',
                'default' => '--',
                'sortable' => false,
        ));
        $this->addColumn('message',
            array(
                'header'  => $this->__('Error Message'),
                'type'    => 'message',
                'height'  => '120px',
                'index'   => 'message',
                'default' => '--',
                'sortable' => false,
        ));

        return parent::_prepareColumns();
    }
}
