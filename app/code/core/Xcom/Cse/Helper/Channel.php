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

class Xcom_Cse_Helper_Channel extends Mage_Core_Helper_Abstract
{
    /**
     * @var array
     */
    protected $_channelTypesOptionHash;


    /**
     * generate options array for grid column param 'option_group'
     *
     * @return array
     */
    public function generateChannelOptions()
    {
        $optionAllChannels = array();
        /** @var $channelCollection Xcom_Cse_Model_Resource_Channel_Collection */
        $channelCollection = Mage::getResourceModel('xcom_cse/channel_collection')
            ->addFieldToFilter('is_active', array('eq' => 1))
            ->setOrder('channeltype_code', Zend_Db_Select::SQL_DESC);

        $optionAllChannels[] = array(
            'value' => 0,
            'label' => $this->__('Magento Website')
        );

        if (!$channelCollection->count()) {
            return $optionAllChannels;
        }

        $processOptionChannel = array();
        $currentChannelCode = '';
        $channelTypes = $this->getChannelTypesOptionHash();

        foreach ($channelCollection as $channel) {
            if ($currentChannelCode != $channel->getChanneltypeCode()) {
                if ($currentChannelCode !== '') {
                    $optionAllChannels[] = array(
                        'label' => $channelTypes[$currentChannelCode],
                        'value' => $processOptionChannel
                    );
                    $processOptionChannel = array();
                }
                $currentChannelCode = $channel->getChanneltypeCode();
            }

            $processOptionChannel[] = array(
                'label' => $channel->getName(),
                'value' => $channel->getChannelId()
            );
        }

        $optionAllChannels[] = array(
            'label' => $channelTypes[$currentChannelCode],
            'value' => $processOptionChannel
        );

        return $optionAllChannels;
    }

    /**
     * Returns hash array of channel types.
     * Example:
     *     array('id' => 'value')
     *
     * @return array
     */
    public function getChannelTypesOptionHash()
    {
        if (null === $this->_channelTypesOptionHash) {
            $this->_channelTypesOptionHash = array();
            $channelTypes = Mage::getSingleton('xcom_channelgroup/config_channeltype')->getAllItems();
            foreach ($channelTypes as $type) {
                $this->_channelTypesOptionHash[$type->getCode()] = $type->getTitle();
            }
        }

        return $this->_channelTypesOptionHash;
    }
}
