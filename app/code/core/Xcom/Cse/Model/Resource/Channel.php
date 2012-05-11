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
 * @package     Xcom_Cse
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Xcom_Cse_Model_Resource_Channel extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('xcom_cse/channel', 'channel_id');
    }

    
    /**
     * Check if channel with the same "Name" already exists.
     *
     * @param Xcom_Cse_Model_Channel $channel
     * @return bool
     */
    public function isChannelNameUnique(Xcom_Cse_Model_Channel $channel)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('count' => new Zend_Db_Expr('COUNT(channel_id)')))
            ->where($this->_getReadAdapter()->quoteIdentifier('name') . ' = ?', $channel->getName());
        if ($channel->getId()) {
            $select->where($this->_getReadAdapter()->quoteIdentifier('channel_id') . ' != ?', $channel->getId());
        }
        return $this->_getReadAdapter()->fetchOne($select) ? false : true;
    }

    /**
     * Check if channel with the same "Offer Name" already exists.
     *
     * @param Xcom_Cse_Model_Channel $channel
     * @return bool
     */
    public function isOfferNameUnique(Xcom_Cse_Model_Channel $channel)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('count' => new Zend_Db_Expr('COUNT(channel_id)')))
            ->where($this->_getReadAdapter()->quoteIdentifier('offer_name') . ' = ?', $channel->getOfferName());
        if ($channel->getId()) {
            $select->where($this->_getReadAdapter()->quoteIdentifier('channel_id') . ' != ?', $channel->getId());
        }
        return $this->_getReadAdapter()->fetchOne($select) ? false : true;
    }
    
    /**
     * Check if channel with the same combination of "Store View", "Site" parameters already exist.
     *
     * @param Xcom_Cse_Model_Channel $channel
     * @return bool
     */
    public function isChannelStoreSiteUnique(Xcom_Cse_Model_Channel $channel)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('count' => new Zend_Db_Expr('COUNT(channel_id)')))
            ->where('store_id = ?', $channel->getStoreId())
            ->where('site_code = ?', $channel->getSiteCode())
            ->where('account_id = ?', $channel->getAccountId());
        return $this->_getReadAdapter()->fetchOne($select) ? false : true;
    }

	/**
	 * Check if "Offer Name" has valid characters.
	 *
     * @param Xcom_Cse_Model_Channel $channel
	 * @return bool
	 */
	public function isOfferNameHasValidCharacters(Xcom_Cse_Model_Channel $channel)
	{
		$offerName = $channel->getOfferName();
		$char_array = array('\\', ' ', '!', '#', '$', '%', '&', "'", '(', ')', '*', '+', '/', ':', ';', 
		                    '<', '>', '?', '@', '[', ']', '^', '{', '}', '|');
		 
		foreach ($char_array as $value) {
			 $pos = strpos($offerName, $value);
			 // Found an invalid character in the filename.
			 if ($pos !== false)
			 	return false;
		}

		return true;
	}

	/**
	 * Check if "Offer Name" has a valid extension.
	 *
     * @param Xcom_Cse_Model_Channel $channel
	 * @return bool
	 */
	public function isOfferNameHasValidExtension(Xcom_Cse_Model_Channel $channel)
	{
		$offerName = $channel->getOfferName();
		$ext_array = array(".xml");
		$extension = strtolower(strrchr($offerName,"."));

		if (!$offerName) {
			return false;
		}
		else {
			foreach ($ext_array as $value) {
				$first_char = substr($value,0,1);
				if ($first_char <> ".")
				   $extensions[] = ".".strtolower($value);
				else
				   $extensions[] = strtolower($value);
			}

			foreach ($extensions as $value) {
				if ($value == $extension)
				   return true;
			}
		}
	}
	
    /**
     * 1. Check if channels with desired account ID are exists.
     * 2. Check if channel is Active|Inactive.
     *
     * @param $accountId
     * @param bool|null $isActive
     * @return bool
     */
    public function validateChannelsByAccountId($accountId, $isActive = null)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('account_id=?', $accountId);
        if ($isActive) {
            $select->where('is_active=?', ($isActive ? 1 : 0));
        }
        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            return true;
        }
        return false;
    }
    
    /**
     * @param $xAccountId string
     * @param $siteId string
     * @param $offerName string
     * @return int|bool
     */
    public function getIdByKey($xAccountId, $siteId, $offerName)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table' => $this->getMainTable()), 'channel_id')
			->where('account_id', $xAccountId)
            ->where('site_code = ?', $siteId)
            ->where('offer_name = ?', $offerName);

        return $this->_getReadAdapter()->fetchOne($select);
    }    
}
