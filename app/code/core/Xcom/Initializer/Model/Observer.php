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
 * @package     Xcom_Initializer
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Initializer_Model_Observer
{

    protected $_responseTopic = '';

    /**
     * Forward to xcomDenied action
     * if no full Xcom data and user try to open one of xcom page except Xcom_Xfabric and Xcom_Stub
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function controllerPreDispatch($observer)
    {
        $controller = $observer->getData('controller_action');
        if (strpos(get_class($controller),'Xcom') === 0
            && strpos($controller->getRequest()->getControllerModule(), 'Xcom_Xfabric') === false
            && strpos($controller->getRequest()->getControllerModule(), 'Xcom_Stub') === false
            && strpos($controller->getRequest()->getControllerModule(), 'Xcom_Initializer') === false
            && strpos($controller->getRequest()->getControllerModule(), 'Xcom_ChannelOrder') === false
            && (strpos($controller->getRequest()->getControllerModule(), 'Xcom_Ebay') === false
                && strpos($controller->getRequest()->getControllerName(), 'ebay_register') === false )
            && $controller->getRequest()->isDispatched()
            && $controller->getRequest()->getActionName() !== 'xcomDenied'
            && $controller->getRequest()->getActionName() !== 'denied'
            && !Mage::helper('xcom_initializer')->isDataCollected()
        ) {
            Mage::getSingleton('adminhtml/session')
                ->setIsUrlNotice($controller->getFlag('', Mage_Adminhtml_Controller_Action::FLAG_IS_URLS_CHECKED));
            $request = $controller->getRequest();
            $request->initForward()
                ->setControllerName('initializer')
                ->setModuleName('admin')
                ->setActionName('xcomDenied')
                ->setDispatched(false);
            return ;
        }
    }

    /**
     * Sends requests to xFabric to collect data
     *
     * @param $observer
     * @return Xcom_Initializer_Model_Observer
     */
    public function runCollectProcess(Varien_Object $observer)
    {
        try {
            $authKey = (bool)Mage::helper('xcom_xfabric')->getResponseAuthorizationKey();
            if (!$authKey) {
                Mage::log("Configuration is not ready. Exit.", null, 'Initializer.log');
                return;
            }

            $jobCollection = Mage::getResourceModel('xcom_initializer/job_collection')
                ->addFieldToFilter('status', Xcom_Initializer_Model_Job::STATUS_PENDING)
                ->addForUpdate();

            Mage::log('Found ' . count($jobCollection) . ' topics', null, 'Initializer.log');

            foreach ($jobCollection as $job) {
                $job->process();
                Mage::log("Topic " . $job->getTopic() . ' '.$job->getMessageParams() .
                    ' was processed.', null, 'Initializer.log');
            }

            Mage::getModel('xcom_initializer/job_params')
                ->generateJobs();

            Mage::getResourceModel('xcom_initializer/job')
                ->reviveExpiredJobs();

        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Update job status on response_message_received event
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function updateJobStatus(Varien_Event_Observer $observer)
    {
        $correlationId = $observer->getEvent()->getMessage()->getCorrelationId();
        if (empty($correlationId)) {
            return;
        }
        Mage::getResourceModel('xcom_initializer/job')
            ->updateStatusByCorrelationId(Xcom_Initializer_Model_Job::STATUS_RECEIVED,
            $correlationId, array('status <= ?' => Xcom_Initializer_Model_Job::STATUS_SENT)
        );
    }

    /**
     * On receiving message step we use the method to update job status and save retrieved data
     * for delayed job generation
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function saveJobParams(Varien_Event_Observer $observer)
    {
        /** @var $data Xcom_Xfabric_Model_Message_Response */
        $responseMessage = $observer->getData('data_object');

        //Verify current topic against topics marked with initializer="prepopulate" attribute
        if (!$this->_validateTopic($responseMessage->getTopic())) {
            return;
        }

        /** @var $jobResource Xcom_Initializer_Model_Resource_Job */
        $jobResource = Mage::getResourceModel('xcom_initializer/job');

        // process failed message
        if (preg_match('/failed$/i', $responseMessage->getTopic())) {
           if (!$responseMessage->getCorrelationId()) {
              return;
           }

            $jobResource->updateStatusByCorrelationId(Xcom_Initializer_Model_Job::STATUS_PENDING,
                $responseMessage->getCorrelationId());
            return;
        }

        /** @var $jobResource Xcom_Initializer_Model_Resource_Jobparams */
        $jobParamsResource = Mage::getResourceModel('xcom_initializer/job_params');

        // process succeeded message
        if (!$responseMessage->getCorrelationId()) {

            $newRowId = $jobResource->addSavedJob($this->_responseTopic,
                'Push Message Received',
                Xcom_Initializer_Model_Job::STATUS_SAVED);

            if (count($responseMessage->dependedMessageData)) {
                $jobParamsResource->addSavedJobParam($newRowId,
                    json_encode($responseMessage->dependedMessageData),
                    Xcom_Initializer_Model_Job_Params::PARAM_NEW);
            }

        } else {
            if (!$responseMessage->getIsProcessed()) {
                return;
            }

            $jobResource->updateStatusByCorrelationId(Xcom_Initializer_Model_Job::STATUS_SAVED,
                $responseMessage->getCorrelationId());
            $jobId = $jobResource->getJobIdByCorrelationId($responseMessage->getCorrelationId());
            if ($jobId && count($responseMessage->dependedMessageData)) {
                    $jobParamsResource->addSavedJobParam($jobId,
                        json_encode($responseMessage->dependedMessageData),
                        Xcom_Initializer_Model_Job_Params::PARAM_NEW);
            }
        }
    }

    /**
     * @param $topic
     * @return bool
     */
    protected function _validateTopic($topic)
    {
        $configTopics = Mage::helper('xcom_xfabric')
            ->getNodeByXpath('*/*[@initializer="prepopulate"]/name');

        foreach ($configTopics as $validTopic) {
            if (strpos($topic, (string)$validTopic) !== false) {
                $this->_responseTopic = (string)$validTopic;
                return true;
            }
        }
    }

    /**
     * Event when taxonomy related data is deleted in
     * Xcom_Mapping_Adminhtml_Map_AttributeController::clearTaxonomyAction
     */
    public function createTaxonomyMessages()
    {
        Mage::getModel('xcom_initializer/job_params')->createTaxonomyMessages();
    }
}
