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
 * @package     Xcom_ChannelOrder
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Xcom_ChannelOrder_Block_Adminhtml_Sales_Order_Grid
    extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    protected function _prepareColumns()
    {
        $addAfter = 'real_order_id';
        if (!Mage::app()->isSingleStoreMode()) {
            $addAfter = 'store_id';
        }

        $this->addColumnAfter('channel', array(
            'header' => $this->__('Channel'),
            'index' => 'channel_id',
            'type' =>'options',
            'filter_condition_callback' => array($this, 'addChannelIdFilter'),
            'width' => '150px',
            'options' => Mage::getModel('xcom_mmp/channel')->getCollection()->toOptionHash(),
            'option_groups' => Mage::helper('xcom_mmp/channel')->generateChannelOptions(),
            'is_use_for_export' => Mage::helper('xcom_ebay')->isOrderExportIncludeCsv(),
            'is_system' => true),

            $addAfter
        );

        return parent::_prepareColumns();
    }

    /**
     * Filter collection in grid by channel_id
     *
     * @param Xcom_ChannelOrder_Model_Resource_Channel_Order_Grid_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Xcom_ChannelOrder_Block_Adminhtml_Sales_Order_Grid
     */
    public function addChannelIdFilter($collection, $column)
    {
        $collection->addChannelIdFilter($column->getFilter()->getValue());
        return $this;
    }

    /**
     * Retrieve Headers row array for Export
     *
     * @return array
     */
    protected function _getExportHeaders()
    {
        $row = array();
        $rowsToEnd = array();
        foreach ($this->getColumns() as $column) {
            if ($column->getIsUseForExport()) {
                $rowsToEnd[] = $column->getExportHeader();
                continue;
            }
            if (!$column->getIsSystem()) {
                $row[] = $column->getExportHeader();
            }
        }
        foreach ($rowsToEnd as $rowToEnd) {
            $row[] = $rowToEnd;
        }
        return $row;
    }

    /**
     * Write item data to csv export file
     *
     * @param Varien_Object $item
     * @param Varien_Io_File $adapter
     */
    protected function _exportCsvItem(Varien_Object $item, Varien_Io_File $adapter)
    {
        $row = array();
        $rowsToEnd = array();
        foreach ($this->getColumns() as $column) {
            if ($column->getIsUseForExport()) {
                $rowsToEnd[] = $column->getRowFieldExport($item);
                continue;
            }
            if (!$column->getIsSystem()) {
                $row[] = $column->getRowFieldExport($item);
            }
        }
        foreach ($rowsToEnd as $rowToEnd) {
            $row[] = $rowToEnd;
        }
        $adapter->streamWriteCsv($row);
    }

    /**
     * Write item data to Excel 2003 XML export file
     *
     * @param Varien_Object $item
     * @param Varien_Io_File $adapter
     * @param Varien_Convert_Parser_Xml_Excel $parser
     */
    protected function _exportExcelItem(Varien_Object $item, Varien_Io_File $adapter, $parser = null)
    {
        if (is_null($parser)) {
            $parser = new Varien_Convert_Parser_Xml_Excel();
        }

        $row = array();
        $rowsToEnd = array();
        foreach ($this->getColumns() as $column) {
            if ($column->getIsUseForExport()) {
                $rowsToEnd[] = $column->getRowFieldExport($item);
                continue;
            }
            if (!$column->getIsSystem()) {
                $row[] = $column->getRowFieldExport($item);
            }
        }
        foreach ($rowsToEnd as $rowToEnd) {
            $row[] = $rowToEnd;
        }

        $data = $parser->getRowXml($row);
        $adapter->streamWrite($data);
    }
}
