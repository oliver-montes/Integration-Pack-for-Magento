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

class Xcom_Initializer_Model_Resource_Job_Params extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('xcom_initializer/job_params', 'param_id');
    }

    /**
     * Select all new records to generate list of jobs
     *
     * @return array
     */
    public function getNewParams()
    {
        $adapter = $this->getReadConnection();
        $select = $adapter->select()
            ->from(array('par' => $this->getTable('xcom_initializer/job_params')), array())
            ->join(array('job' => $this->getTable('xcom_initializer/job')),
                'job.job_id = par.job_id', array())
            ->where('par.status = ?', Xcom_Initializer_Model_Job_Params::PARAM_NEW)
            ->columns(array(
                'param_id'       => 'par.param_id',
                'topic'          => 'job.topic',
                'message_params'   => 'par.message_params',
            ))
            ->forUpdate(true);
        return $adapter->fetchAssoc($select);
    }

    /**
     * @param $param_id
     * @param $status
     */
    public function updateStatus($param_id, $status)
    {
        $adapter = $this->_getWriteAdapter();
        $where = $adapter->quoteInto('param_id = ?', $param_id);
        $adapter->update($this->getMainTable(), array('status' => $status), $where);
    }

    /**
     * Insert new job param
     *
     * @param $jobId
     * @param $messageParams
     * @param $status
     * @return string
     */
    public function addSavedJobParam($jobId, $messageParams, $status)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->insert($this->getMainTable(), array(
            'job_id'         => $jobId,
            'message_params' => $messageParams,
            'status'         => $status
        ));
        return $adapter->lastInsertId();
    }
}
