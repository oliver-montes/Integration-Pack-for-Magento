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
 * @package     Xcom_Xfabric
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Xcom_Xfabric_Model_Debug_Visitor_Save implements Xcom_Xfabric_Model_Debug_VisitorInterface
{
    /**
     *
     * Save into Database service request call event
     *
     * @param object $node Xcom_Xfabric_Model_Debug_NodeInterface
     */
    public function start(Xcom_Xfabric_Model_Debug_NodeInterface $node)
    {
        $resource = Mage::getResourceSingleton('xcom_xfabric/debug_node');
        $resource->insert($node);

        return $this;
    }

    public function stop(Xcom_Xfabric_Model_Debug_NodeInterface $node, $hasErrors = false)
    {
        $resource = Mage::getResourceSingleton('xcom_xfabric/debug_node');
        $resource->update($node, $hasErrors);

        return $this;
    }
}
