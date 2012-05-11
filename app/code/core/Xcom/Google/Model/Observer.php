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
class Xcom_Google_Model_Observer
{
    /**
     * Save system config event 
     *
     * @param Varien_Object $observer
     */
    public function saveSystemConfig($observer)
    {   
		$groups['google']['fields']['cron_schedule']['value'] = $this->_getSchedule();

        Mage::getModel('adminhtml/config_data')
                ->setSection('xcom_channel')
                ->setGroups($groups)
                ->save();
    }

    /**
     * Transform system settings option to cron schedule string
     * 
     * @return string
     */
    protected function _getSchedule()
    {
        $data = Mage::app()->getRequest()->getPost('groups');

        $frequency = !empty($data['google']['fields']['feed_export_frequency']['value'])?
                         $data['google']['fields']['feed_export_frequency']['value']:
                         0;
        $time     = !empty($data['google']['fields']['feed_export_start_time']['value'])?
                         $data['google']['fields']['feed_export_start_time']['value']:
                         0;
        $hour = $time[0];
        $minute = $time[1];
        
        $schedule = "$minute $hour ";
        
        switch ($frequency) {
            case Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY:
                $schedule .= "* * *"; 
                break;
            case Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY:
                $schedule .= "* * 1"; 
                break;
            case Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY:
                $schedule .= "1 * *"; 
                break;
            default:
                $schedule .= "* */1 *"; 
                break;
        }

        return $schedule;
    }
    
    /**
     * @param Varien_Object $observer
     * @return Xcom_Google_Model_Observer
     */
    public function sendGoogleOffer($observer)
    {
    	// If automatic, send the google offer to all enabled feeds.
    	$isAutomatic = Mage::getStoreConfig('xcom_channel/google/feed_export_automatic');
    	if (!$isAutomatic) {
        	return $this;
        }   
          
		// Loop through all enabled channels.
		$channels = Mage::getResourceModel('xcom_cse/channel_collection')
            ->addChanneltypeCodeFilter(Mage::helper('xcom_google')->getChanneltypeCode())
            ->addFieldToFilter('is_active', 1);
        	
        $helper = Mage::helper('xcom_cseoffer');
        
        foreach ($channels as $channel) {
           	$offerData = array('channel_id' => $channel->getId());

           	// Get all products that:
           	//		- are enabled
           	// 		- are in stock
           	//		- have an image specified.
           	
			$products = Mage::getModel('catalog/product')->getCollection()
            	->addStoreFilter($channel->getStoreId())
            	->addFieldToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
           	
	        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products);
            
	        $productIds = $products->getAllIds();

       		$productIdsWithImages;
    
	        $arrayIdx = 0;
	        foreach($productIds as $productId) {
	        	$product = Mage::getModel('catalog/product')
                	->load($productId);
                	
                if ($this->_isProductImageSpecified($product)) {
					$productIdsWithImages[$arrayIdx] = $productId;
					$arrayIdx++;
                }	                
	        }
	        	        
           	$helper->processOffers($channel, $productIdsWithImages, $offerData);
      	}
    }
    
    /**
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    protected function _isProductImageSpecified(Mage_Catalog_Model_Product $product)
    {
        $image = $product->getData('image');
        return ($image && $image !='no_selection');
    }
}