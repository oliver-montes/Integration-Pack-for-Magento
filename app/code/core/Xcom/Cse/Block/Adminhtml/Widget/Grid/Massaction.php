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
class Xcom_Cse_Block_Adminhtml_Widget_Grid_Massaction
    extends Mage_Adminhtml_Block_Widget_Grid_Massaction
{
    /**
     * Prepare javascript object for grid massaction.
     *
     * @return string
     */
    public function getJavaScript()
    {
        return "
                {$this->getJsObjectName()} = new xcomGridMassaction('{$this->getHtmlId()}',
                " . "{$this->getGridJsObjectName()}, '{$this->getSelectedJson()}',
                " . "'{$this->getFormFieldNameInternal()}', '{$this->getFormFieldName()}');
                {$this->getJsObjectName()}.setItems({$this->getItemsJson()});
                {$this->getJsObjectName()}.setGridIds('{$this->getGridIdsJson()}');
                ". ($this->getUseAjax() ? "{$this->getJsObjectName()}.setUseAjax(true);" : '') . "
                ". ($this->getUseSelectAll() ? "{$this->getJsObjectName()}.setUseSelectAll(true);" : '') .
                "{$this->getJsObjectName()}.errorText = '{$this->getErrorText()}';";
    }
}
