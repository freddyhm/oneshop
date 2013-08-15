<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Raf
 * @version    2.0.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Raf_Block_Adminhtml_Transaction_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('RafTransactionsGrid');
        $this->setDefaultSort('transaction_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('awraf/rule_action_transaction')
                ->getCollection();

        $collection->getSelect()->join(array('customers' => $collection->getTable('customer/entity')), 'main_table.customer_id = customers.entity_id', array('customers.email'))
                ->joinLeft(array('rules' => $collection->getTable('awraf/rule')), 'main_table.rule_id = rules.rule_id', array('rules.rule_name'));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('transaction_id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'transaction_id',
        ));

        $this->addColumn('email', array(
            'header' => $this->__('Customer Email'),
            'filter_index' => 'customers.email',
            'index' => 'email',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header' => $this->__('Websites'),
                'width' => '100px',
                'sortable' => false,
                'index' => 'website_id',
                'type' => 'options',
                'filter_index' => 'main_table.website_id',
                'options' => Mage::getModel('core/website')->getCollection()->toOptionHash(),
            ));
        }

        $this->addColumn('discount',
            array(
                'header'=> $this->__('Transaction Amount'),
                'type'  => 'price',
                'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
                'index' => 'discount'
        )); 
        
         $this->addColumn('rule_name', array(
            'header' => $this->__('Related To Rule'),
            'index' => 'rule_name',
            'default' => '--',    
            'is_system' => true,
            'renderer' => 'awraf/adminhtml_grid_renderer_ruleName',
            'filter_index' => 'rules.rule_name'        
        )); 
         
        $this->addColumn('comment',
            array(
                'header'=> $this->__('Comment'),               
                'index' => 'comment',
                'type' => 'text', 
                'truncate' => 100,
                'default' => '--',
                'width' => '550px'
        )); 
   
        $this->addColumn('created_at', array(
            'header' => $this->__('Created At'),
            'index' => 'created_at', 
            'width' => '170px', 
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime', 
            'gmtoffset' => true,
            'default' => ' ---- '
        )); 

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportXml', $this->__('XML'));

        return parent::_prepareColumns();
    }

    protected function filterStore($collection, $column)
    {
        $val = $column->getFilter()->getValue();

        if (!$val = $column->getFilter()->getValue()) {
            return;
        }

        $collection->getSelect()
                ->where("FIND_IN_SET('$val', {$column->getIndex()}) OR FIND_IN_SET('0', {$column->getIndex()})");
    }

    public function getRowUrl($row)
    {
        return null;
    }
 
}
