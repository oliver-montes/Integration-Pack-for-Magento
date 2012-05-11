<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Grid column widget for rendering action grid cells
 *
 * @category   Xcom
 * @package    Xcom_Google
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Xcom_Google_Block_Adminhtml_Widget_Grid_Column_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
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

        parent::render($row);
    }

    /**
     * Render single action as text
     *
     * @param array $action
     * @param Varien_Object $row
     * @return string
     */
    protected function _toTextHtml($action, Varien_Object $row)
    {
        $actionCaption = '';
        $this->_transformActionData($action, $actionCaption, $row);

        $actionAttributes = new Varien_Object();
        $actionAttributes->setData($action);
        return '<span ' . $actionAttributes->serialize() . '>' . $actionCaption . '</span>';
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
        if (isset($action['caption']) &&
            !empty($action['onclick']) && is_array($action['onclick'])) {
            $onclick  = 'javascript:' . $action['onclick']['method'] . '(';
            $params = array($action['field']=>$this->_getValue($row));
            if (isset($action['onclick']['params'])) {
                $params = array_merge($action['onclick']['params'], $params);
                $onclick  .= $this->_getParameterValues($params);
            }
            $onclick  .= '); return false;';
            $action['onclick']  = $onclick;
            $actionCaption = $action['caption'];
            unset($action['field']);
            unset($action['caption']);
            return $this;
        } else {
            return parent::_transformActionData($action, $actionCaption, $row);
        }
    }

    /**
     * @param array $params
     *
     * @return string
     */
    protected function _getParameterValues($params)
    {
        $result = '';
        foreach ($params as $value) {
            $result .= '\'' . $value . '\',';
        }
        return rtrim($result, ',');
    }
}
