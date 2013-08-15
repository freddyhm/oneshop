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


class AW_Raf_Block_Adminhtml_Rules_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('RafRulesGrid');
        $this->setDefaultSort('rule_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    } 

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('awraf/rule')
                ->getCollection();

        $this->setCollection($collection);

        parent::_prepareCollection();
        
        $this->addAdditionalFields();

    }

    protected function _prepareColumns()
    {
        $this->addColumn('rule_id', array(
            'header' => $this->__('Rule Id'),
            'align' => 'right',
            'width' => '50px',           
            'index' => 'rule_id'
        )); 

        $this->addColumn('rule_name', array(
            'header' => $this->__('Rule Name'),
            'index' => 'rule_name'
        ));

        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                $this->__('Disabled'),
                $this->__('Enabled')
            ),
        ));

        $this->addColumn('target', array(
            'header' => $this->__('Rule Target'),
            'index' => 'target',
            'type' => 'number'
        ));
        
        $this->addColumn('action_amount', array(
            'header' => $this->__('Discount Amount'),
            'index' => 'action',
            'filter_index' => 'action',
            'type' => 'number'
        ));

        $this->addColumn('action_type', array(
            'header' => $this->__('Discount Type'),
            'index' => 'action_type',
            'type' => 'options',
            'options' => Mage::getModel('awraf/config_source_actionType')->toOptionArray()
        ));

        $this->addColumn('stop_on_first', array(
            'header' => $this->__('Stop Further Rules Processing'),
            'index' => 'stop_on_first',
            'type' => 'options',
            'options' => array(
                $this->__('No'),
                $this->__('Yes')               
            ),
        ));

        $this->addColumn('priority', array(
            'header' => $this->__('Rule Priority'),
            'index' => 'priority'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_ids', array(
                'header' => $this->__('Store View'),
                'index' => 'store_ids',
                'type' => 'store',
                'store_all' => true,
                'store_view' => true,
                'sortable' => false,
                'filter_condition_callback' => array($this, 'filterStore'),
            ));
        }


        $this->addColumn('created_at', array(
            'header' => $this->__('Created At'),
            'index' => 'created_at',
            'width' => '170px',
            'type' => 'datetime',
            'gmtoffset' => true,
            'default' => ' ---- '
        ));
      
        $this->addColumn('active_from', array(
            'header' => $this->__('Active From'),
            'index' => 'active_from',
            'width' => '170px',
            'type' => 'datetime',
            'gmtoffset' => true,
            'default' => ' ---- '
        ));
        
        
        $this->addColumn('action',
            array(
                'header'    => $this->__('Action'),
                'width'     => '150px',
                'type'      => 'action',               
                'getter'     => 'getRuleId',
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

    protected function addAdditionalFields()
    {
        foreach ($this->getCollection() as $item) {
            $item->setData('store_ids', explode(',', $item->getData('store_ids')));
        }
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rule_id');
        $this->getMassactionBlock()->setFormFieldName('rules');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => $this->__('Are you sure?')
        ));
    }

}
