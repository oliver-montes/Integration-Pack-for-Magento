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
 * @package     Xcom_Chronicle
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Chronicle_Model_Message_Product_Get_Inbound extends Xcom_Xfabric_Model_Message_Response
{

    const PRODUCT_ID_TYPE_SKU = "SKU";
    const PRODUCT_ID_TYPE_ID = "PRODUCT_ID";

    /**
     * Initialization of class
     */
    protected function _construct()
    {
        $this->_topic               = 'com.x.pim.v1/ProductLookup/LookupProduct';
        $this->_schemaRecordName    = 'LookupProduct';
        $this->_schemaVersion       = "1.0.0";

        parent::_construct();
    }

    /**
     * Process data on message received
     * @return Xcom_Chronicle_Model_Message_Order_Search_Inbound
     */
    public function process()
    {
        parent::process();
        $data = $this->getBody();
        if (!isset($data)) {
            $data = array();
        }
        try {
            if ($this->_validateSchema()) {
                $resultSet = $this->_processSearchQuery($data);

                if(sizeof($resultSet['found'])>0){
                    $response = array(
                        'products' => $resultSet['results'],
                        'locales' => $data['locales'],
                        'filter' => $data['filter'],
                        'destination_id' => $this->getPublisherPseudonym(),
                    );
                    Mage::helper('xcom_xfabric')->send('com.x.pim.v1/ProductLookup/LookupProductSucceeded', $response);
                }
                if(sizeof($resultSet['notFound'])>0){
                    $response = array(
                        'ids' => $resultSet['notFound'],
                        'filter' => $data['filter'],
                        'locales'=> $data['locales'],
                        'errors'=>$resultSet['errors'],
                        'destination_id' => $this->getPublisherPseudonym(),
                    );
                    Mage::helper('xcom_xfabric')->send('com.x.pim.v1/ProductLookup/LookupProductFailed',$response);
                }
            }
        }
        catch(Exception $ex){
            Mage::logException($ex);
            $errorResponse = $this->_generate_failure_data($data['ids'],$data['locales'],$data['filter'],$ex);
            Mage::helper('xcom_xfabric')->send('com.x.pim.v1/ProductLookup/LookupProductFailed', $errorResponse);
        }
        return $this;
    }

    protected function _getProductById($id){
        $products = Mage::getResourceModel('catalog/product_collection')
            ->addFieldToFilter('entity_id',$id);
        $products->load();
        if(count($products)==1){
            foreach($products as $product){
                $p = Mage::getModel('catalog/product')->load((int)$id);
                return Mage::getModel('xcom_chronicle/message_product', $p)->toArray();
            }
        }
    }

    protected function _getProductBySku($id){
        $products = Mage::getResourceModel('catalog/product_collection')
            ->addFieldToFilter('sku', $id);
        $products->load();
        if(count($products)==1){
            foreach($products as $product){
                $p = Mage::getModel('catalog/product')->load((int)$product->getId());
                return Mage::getModel('xcom_chronicle/message_product', $p)->toArray();
            }
        }
    }

    /**
     * Save/update return policy data to DB
     *
     * @param array $data
     * @return array
     */
    protected function _processSearchQuery(&$data)
    {
        $results = array();
        $found = array();
        $notFound = array();
        $errors = array();
        foreach ($data['ids'] as $id) {
            $result = null;
            try{
            if($id['type']==self::PRODUCT_ID_TYPE_ID){
                $result = $this->_getProductById($id['value']);

            }
            elseif($id['type']==self::PRODUCT_ID_TYPE_SKU){
                $result = $this->_getProductBySku($id['value']);
            }
            if(is_null($result)){
                $notFound[] = $id;
                $errors[] = array(
                  'code' => '-1',
                  'message' => 'Product not found',
                  'parameters' => null,
                );
            }
            else{
                $results[] =$result;
                $found[] = $id;
            }
            }
            catch(Exception $ex){
                Mage::logException($ex);
                $notFound[] = $id;
                $errors[] = array(
                    'code' => '-1',
                    'message' => $ex->getMessage(),
                    'parameters' => null
                );
            }
        }
        $resultSet = array(
          'results' => $results,
            'found' => $found,
            'notFound'=>$notFound,
            'errors' =>$errors,
        );
        return $resultSet;
    }

    protected function _generate_failure_data($ids,$locale,$filter,$ex)
    {
        $errorResponse = array(
            'ids' => $ids,
            'locale' => $locale,
            'filter' => $filter,
            'errors' => array(
                array(
                    'code' => '-1',
                    'message' => $ex->getMessage(),
                    'parameters' => null
                )
            ),
            'destination_id' => $this->getPublisherPseudonym(),
        );
        return $errorResponse;
    }
}
