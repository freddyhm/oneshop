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


class AW_Raf_Block_Adminhtml_Bonus_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();       
        $this->setId('RafBonusGrid');
        $this->setDefaultSort('item_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {      
        $collection = Mage::getModel('awraf/rule_action_discount')
                ->getCollection();
        
        $collection->getSelect()->join(array('customers' => $collection->getTable('customer/entity')), 
                'main_table.customer_id = customers.entity_id', array('customers.email'))
                ->joinLeft(array('rules' => $collection->getTable('awraf/rule')), 
                 'main_table.rule_id = rules.rule_id', array('rules.rule_name'));
       
        $this->setCollection($collection);
 
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('item_id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'item_id',
        ));

        $this->addColumn('email', array(
            'header' => $this->__('Customer Email'),              
            'filter_index' => 'customers.email',
            'index' => 'email',
        ));

       if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id',
                array(
                    'header'=> $this->__('Websites'),
                    'width' => '100px',
                    'sortable'  => false,
                    'index'     => 'website_id',
                    'type'      => 'options',
                    'filter_index' => 'main_table.website_id',
                    'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
            ));
        }
        
        $this->addColumn('discount', array(
            'header' => $this->__('Discount Amount, %'),
            'index' => 'discount',
            'type'  => 'number'           
        ));
 
        /*
        $this->addColumn('type', array(
            'header' => $this->__('Discount Type'),
            'index' => 'type',
            'type' => 'options',
            'filter_index' => 'main_table.type',
            'options' => Mage::getModel('awraf/config_source_actionType')->toOptionArray()
        ));*/
        
        $this->addColumn('trig_qty', array(
            'type' => 'number',
            'header' => $this->__('Trigger Count'),
            'index' => 'trig_qty'            
        ));
        
        $this->addColumn('rule_name', array(
            'header' => $this->__('Related To Rule'),
            'index' => 'rule_name',
            'is_system' => true,
            'renderer' => 'awraf/adminhtml_grid_renderer_ruleName',
            'default' => '--',            
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
            'type' => 'datetime',
            'filter_index' => 'main_table.created_at',
            'gmtoffset' => true,
            'default' => ' ---- '
        ));
 
        $this->addColumn('action',
            array(
                'header'    => $this->__('Action'),
                'width'     => '150px',
                'type'      => 'action',               
                'getter'     => 'getItemId',
                'actions'   => array(
                    array(
                        'caption' => $this->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit'                           
                        ),
                        'field'   => 'id'
                    ),
                    array(
                        'caption' => $this->__('Delete'),
                        'url'     => array(
                            'base'=>'*/*/delete'                           
                        ),
                        'field'   => 'id',
                        'confirm' => $this->__('Are you sure?')
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
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
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('item_id');
        $this->getMassactionBlock()->setFormFieldName('rules');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => $this->__('Are you sure?')
        ));
    }

}
