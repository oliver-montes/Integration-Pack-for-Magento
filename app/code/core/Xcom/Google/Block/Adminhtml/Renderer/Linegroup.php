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
 * @category    Xcom
 * @package     Xcom_Google
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_Google_Block_Adminhtml_Renderer_Linegroup
    extends Varien_Data_Form_Element_Abstract
{
    /**
     *
     */
    public function _construct() {
        $elements = $this->getData('elements');
        if ($elements) {
            foreach ($elements as $item) {
                list($class, $type, $config) = $item;
                $this->addField($class, $type, $config);
            }
        }

    }


    /**
     * Get html output
     * @return string
     */
    public function getElementHtml()
    {
        $toHtml = '';
        foreach ($this->getElements() as $element) {
            $toHtml .= '<span>' . $element->toHtml() . '</span>';
        }
        return $toHtml;
    }


}

