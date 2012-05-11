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
 * @package     Xcom_ChannelGroup
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_ChannelGroup_Model_Config_Channeltype
{
    /**
     * Path to Channel Group data.
     */
    const XML_PATH_XCOM_CHANNEL_GROUP        = 'xcom/channel/group';

    /**
     * Path to Channel Type data.
     */
    const XML_PATH_XCOM_CHANNEL_TYPE         = 'xcom/channel/type';

    /**
     * Path to channeltype by default.
     */
    const XML_PATH_XCOM_CHANNEL_TYPE_DEFAULT  = 'xcom/channel/type/default';

    /**
     * Type identifier for Channel Group objects.
     */
    const GROUP_ID = 'group';

    /**
     * Type identifier for Channel Type objects.
     */
    const TYPE_ID = 'type';

    /**
     * Default Channel Type object.
     *
     * @var Varien_Object
     */
    protected $_default;

    /**
     * Array of sorted Channel Group objects.
     *
     * @var array
     */
    protected $_groups;

    /**
     * Array of sorted Channel Types objects.
     *
     * @var array
     */
    protected $_types;

    /**
     * Array of Channel Types objects sorted and grouped by Channel Groups objects.
     *
     * @var array
     */
    protected $_typesByGroup;

    /**
     * Array of sorted Channel Group and Channel Type objects.
     *
     * @var array
     */
    protected $_tabs;

    /**
     * Config tags, that doesn't parsed as Channel Type or Channel Group objects.
     *
     * @var array
     */
    protected $_ignoredTags = array('default');

    /**
     * Get Channel Type from config by code.
     *
     * @param string $code
     * @return Varien_Object
     */
    public function getChanneltype($code = null)
    {
        if (!$this->_types) {
            $this->getAllChannelTypes();
        }
        if (!is_null($code) && isset($this->_types[$code])) {
            return $this->_types[$code];
        }
        return new Varien_Object();
    }

    /**
     * Callback function for channeltypes sorting (see getAllTabs()).
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sort($a, $b)
    {
        if (!isset($a['sort_order']) || !isset($b['sort_order'])) {
            return -1;
        }
        return strnatcmp($a['sort_order'], $b['sort_order']);
    }


    /**
     * Prepare and retrieve array of sorted Channel Type objects and Channel Group objects.
     *
     * @return array
     */
    public function getAllTabs()
    {
        if (!$this->_tabs) {
            $this->_tabs = array();
            foreach ($this->getAllChannelGroups() as $channelgroup) {
                if (!$this->isValid($channelgroup)) {
                    continue;
                }
                $this->_tabs[$channelgroup->getCode()] = $channelgroup;
                foreach ($this->getChannelTypesByGroup($channelgroup->getCode()) as $channeltype) {
                    if (!$this->isValid($channeltype)) {
                        continue;
                    }
                    $this->_tabs[$channeltype->getCode()] = $channeltype;
                }
            }
        }
        return $this->_tabs;
    }

    /**
     * Retrieve Channel Group objects from config.
     *
     * @return array
     */
    public function getAllChannelGroups()
    {
        if (!$this->_groups) {
            $data = Mage::getStoreConfig(self::XML_PATH_XCOM_CHANNEL_GROUP);
            if (is_array($data)) {
                uasort($data, array($this, "_sort"));
                $this->_groups = array();
                foreach ($data as $key => $val) {
                    $object = new Varien_Object($val);
                    $object->setCode($key);
                    $object->setType(self::GROUP_ID);
                    $this->_groups[$key] = $object;
                }
            }
        }
        return $this->_groups;
    }

    /**
     * Retrieve Channel Type objects from config.
     *
     * @return array
     */
    public function getAllChannelTypes()
    {
        if (!$this->_types) {
            $data = Mage::getStoreConfig(self::XML_PATH_XCOM_CHANNEL_TYPE);
            if (is_array($data)) {
                foreach ($this->_ignoredTags as $tagName) {
                    unset($data[$tagName]);
                }
                uasort($data, array($this, "_sort"));
                $this->_types = array();
                $this->_typesByGroup = array();
                foreach ($data as $key => $val) {
                    $object = new Varien_Object($val);
                    $object->setCode($key);
                    $object->setType(self::TYPE_ID);
                    $this->_types[$key] = $object;
                    $this->_typesByGroup[$object->getGroup()][$key] = $object;
                }
            }
        }
        return $this->_types;
    }

    /**
     * Retrieve array of Channel Type objects by Group code.
     *
     * @param string $group
     * @return array
     */
    public function getChannelTypesByGroup($group = '')
    {
        if (!$this->_typesByGroup) {
            $this->getAllChannelTypes();
        }
        if (!empty($group) && isset($this->_typesByGroup[$group])) {
            return $this->_typesByGroup[$group];
        }
        return array();
    }

    /**
     * Check if Channeltype has a required parameters.
     *
     * @param Varien_Object $object
     * @return boolean
     */
    public function isValid(Varien_Object $object)
    {
        if ($object->isEmpty()) {
            return false;
        }
        switch ($object->getType()) {
            case self::GROUP_ID:
                $required = array('title', 'code');
                break;

            case self::TYPE_ID:
                $required = array('title', 'module', 'code');
                break;

            default:
                $required = array();
        }
        foreach ($required as $val) {
            $data = $object->getData($val);
            if (empty($data)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get channelltype by defautl from config.
     *
     * @return Varien_Object
     */
    public function getDefault()
    {
        if (!$this->_default) {
            if (!$this->_types) {
                $this->getAllChannelTypes();
            }
            $code = Mage::getStoreConfig(self::XML_PATH_XCOM_CHANNEL_TYPE_DEFAULT);
            if (isset($this->_types[$code])) {
                $this->_default = $this->_types[$code];
            } else {
                $this->_default = new Varien_Object();
            }
        }
        return $this->_default;
    }
}
