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
 * @package     Xcom_Ititializer
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Initializer_Model_Job_Params extends Mage_Core_Model_Abstract
{
    /**
     * Parameter table statuses
     */
    const PARAM_NEW        = 1;
    const PARAM_PROCESSED  = 2;

    /**
     * Initialize class
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('xcom_initializer/job_params');
    }

    /**
     * Generate new jobs
     *
     * We selects all non-generated records from the job_params table
     * and create new jobs.
     *
     * @return bool
     */
    public function generateJobs()
    {
        $jobParams = $this->getResource()->getNewParams();
        foreach ($jobParams as $param) {
            $result = false;
            switch ($param['topic']) {
                case 'marketplace/site/search' :
                    $result = $this->_generateSiteRelatedJobs($param);
                    break;

                case 'marketplace/environment/search' :
                    $result = $this->_generateMarketplaceJobs($param);
                    break;
            }
            if ($result) {
                $this->getResource()->updateStatus($param['param_id'], self::PARAM_PROCESSED);
            }
        }
        return $this;
    }

    /**
     * Send taxonomy messages productTaxonomy/get and productTaxonomy/productType/get
     */
    public function createTaxonomyMessages()
    {
        $hardcodedTopics = array("productTaxonomy/get", "productTaxonomy/productType/get");
        $locales = array(
            array('country' => 'US',  'language'=> 'en'),
            array('country' => 'GB',  'language'=> 'en'),
            array('country' => 'DE',  'language'=> 'de'),
            array('country' => 'FR',  'language'=> 'fr'),
            array('country' => 'AU',  'language'=> 'en'),
        );

        foreach ($hardcodedTopics as $topic) {
            foreach ($locales as $locale) {
                $job = Mage::getModel('xcom_initializer/job')
                    ->setTopic($topic)
                    ->setStatus(Xcom_Initializer_Model_Job::STATUS_PENDING)
                    ->setMessageParams(json_encode($locale))
                    ->save();
            }
        }
    }

    /**
     * Create list of jobs related to the topic marketplace/site/search
     *
     * @param array $param
     * @return bool
     */
    private function _generateSiteRelatedJobs(array $param)
    {
        $topics = array(
            'marketplace/environment/search',
            'marketplace/currency/search'
        );
        $data = json_decode($param['message_params'], true);
        foreach ($topics as $topic) {
            foreach ($data['siteCodes'] as $siteCode) {
                Mage::getModel('xcom_initializer/job')
                    ->setTopic($topic)
                    ->setStatus(Xcom_Initializer_Model_Job::STATUS_PENDING)
                    ->setMessageParams(json_encode(array(
                        'siteCode' => $siteCode
                    )))
                    ->save();
            }
        }
        return true;
    }

    /**
     * Create list of jobs related to the topic marketplace/environment/search
     *
     * @param array $param
     * @return bool
     */
    private function _generateMarketplaceJobs(array $param)
    {
        $topics = array(
            'marketplace/shippingService/search',
            'marketplace/paymentMethod/search',
            'marketplace/country/search',
            'marketplace/handlingTime/search',
            'marketplace/returnPolicy/get',
            'marketplace/category/search',
        );

        $data = json_decode($param['message_params'], true);
        foreach ($topics as $topic) {
            foreach ($data['environments'] as $entry) {
                Mage::getModel('xcom_initializer/job')
                    ->setTopic($topic)
                    ->setStatus(Xcom_Initializer_Model_Job::STATUS_PENDING)
                    ->setMessageParams(json_encode(array(
                            'siteCode' => $data['siteCode'],
                            'environmentName' => $entry['name']
                    )))
                    ->save();
            }
        }
        return true;
    }
}
