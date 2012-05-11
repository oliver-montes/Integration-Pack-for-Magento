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

/**
 * Grid column widget for rendering grid cells that contains mapped values.
 *
 * @category   Xcom
 * @package    Xcom_Cse
 */
class Xcom_Cse_Block_Adminhtml_Widget_Grid_Column_Renderer_Multiple_Actions
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    protected $_rowValueKey;
    /**
     * Renders column
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $actions = $this->getColumn()->getActions();
        if ( empty($actions) || !is_array($actions) ) {
            return '&nbsp';
        }

        if(!$this->getColumn()->getNoLink()) {
            $options = $this->getColumn()->getOptions();
            $data = $row->getData($this->getColumn()->getId());
            if (is_array($data)) {
                $res = array();
                foreach ($data as $key => $value) {
                    $this->_rowValueKey = $key;
                    if (!empty($options[$value])) {
                        $action = $this->_getActionItem($value);
                        if ( is_array($action) ) {
                            if($action['type'] == 'text') {
                                $res[] = $action['caption'];
                            } else {
                                $res[] = $this->_toLinkHtml($action, $row);
                            }
                        }
                    }
                    $this->_rowValueKey = null;
                }
                return implode('<br/>', $res);
            }
        }
        return '';
    }

    /**
     * Retrieve action from actions array.
     *
     * @param null $value
     * @return array
     */
    protected function _getActionItem($value = null)
    {
        if (is_null($value)) {
            return array();
        }
        $actions = $this->getColumn()->getActions();
        if (is_array($actions)) {
            foreach ($actions as $action) {
                if ($action['option_id'] == $value) {
                    return $action;
                }
            }
        }
        return array();
    }

    /**
     * Prepares action data for html render
     *
     * @param array $action
     * @param string $actionCaption
     * @param Varien_Object $row
     * @return Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
     */
    protected function _transformActionData(&$action, &$actionCaption, Varien_Object $row)
    {
        $this->getColumn()->setFormat(null);

        foreach ( $action as $attibute => $value ) {
            switch ($attibute) {
                case 'option_id':
                    unset($action['option_id']);
                    break;

                case 'caption':
                    $actionCaption = $action['caption'];
                    unset($action['caption']);
                       break;

                case 'url':
                    if(is_array($action['url'])) {
                        if (is_array($action['field'])) {
                            $params = array();
                            foreach ($action['field'] as $fieldKey => $fieldValue) {
                                $rowValue = $row->getData($fieldValue);
                                if ($this->_isValueArray($rowValue)) {
                                    $rowValue = $rowValue[$this->_rowValueKey];
                                }
                                $params[$fieldKey] = $rowValue;
                            }
                        } else {
                            $params = array($action['field']=>$this->_getValue($row));
                        }
                        if(isset($action['url']['params'])) {
                            $params = array_merge($action['url']['params'], $params);
                        }
                        $action['href'] = $this->getUrl($action['url']['base'], $params);
                        unset($action['field']);
                    } else {
                        $action['href'] = $action['url'];
                    }
                    unset($action['url']);
                       break;

                case 'popup':
                    $action['onclick'] = 'popWin(this.href, \'_blank\',' .
                                         ' \'width=800,height=700,resizable=1,scrollbars=1\');return false;';
                    break;

            }
        }
        return $this;
    }

    /**
     * @param mixed $rowValue
     * @return bool
     */
    protected function _isValueArray($rowValue)
    {
        return !is_null($this->_rowValueKey) && is_array($rowValue) && !empty($rowValue[$this->_rowValueKey]);
    }
}
