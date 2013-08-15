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


class AW_Raf_Block_Adminhtml_Rules_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
         
        $rule = Mage::registry('awraf_rule');        
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $this->__('General Information')));
        
        if($rule->getType() == AW_Raf_Model_Rule::SIGNUP_TYPE) {
            $rule->setTarget((int) $rule->getTarget());
        }
        
        if($rule->getId()) {       
            $rule->setTypeLabel(Mage::getModel('awraf/config_source_ruleType')->toLabel($rule->getType()));            
            $fieldset->addField('type_label', 'label', array(
                'label' => $this->__('Rule Type')                 
            )); 
            
            $rule->setBonusTypeLabel(Mage::getModel('awraf/config_source_actionType')->toLabel(
                    $rule->getType(), $rule->getActionType())); 
            $fieldset->addField('bonus_type_label', 'label', array(
                'label' => $this->__('Bonus Type')                 
            ));            
            $rule->setActiveFromLabel(Mage::app()->getLocale()->date($rule->getData('active_from'), Varien_Date::DATETIME_INTERNAL_FORMAT)); 
            $fieldset->addField('active_from_label', 'label', array(
                'label' => $this->__('Active From')                 
            ));         
        }
        
        $fieldset->addField('rule_name', 'text', array(
            'label' => $this->__('Rule Name'),
            'title' => $this->__('Rule Name'),
            'name' => 'rule_name'                
        ));
        
        if(!$rule->getId()) {
            $fieldset->addField('type', 'select', array(
                'label' => $this->__('Rule Type'),
                'title' => $this->__('Rule Type'),
                'name' => 'type',
                'options' => Mage::getModel('awraf/config_source_ruleType')->toOptionArray()
            ));       
            $fieldset->addField('type_' . AW_Raf_Model_Rule::SIGNUP_TYPE, 'select', array(
                'label' => $this->__('Bonus Type'),
                'title' => $this->__('Bonus Type'),     
                'class' => 'awraf-action-type',
                'name' => 'type_' . AW_Raf_Model_Rule::SIGNUP_TYPE,
                'options' => Mage::getSingleton('awraf/config_source_actionType')->toOptionArray('signup')
            ));        
            $fieldset->addField('type_' . AW_Raf_Model_Rule::ORDER_AMOUNT_TYPE, 'select', array(
                'label' => $this->__('Bonus Type'),
                'title' => $this->__('Bonus Type'),
                'class' => 'awraf-action-type',
                'name' => 'type_' . AW_Raf_Model_Rule::ORDER_AMOUNT_TYPE,
                'options' => Mage::getSingleton('awraf/config_source_actionType')->toOptionArray('amount')
            ));         
            $fieldset->addField('type_' . AW_Raf_Model_Rule::ORDER_ITEM_QTY_TYPE, 'select', array(
                'label' => $this->__('Bonus Type'),
                'title' => $this->__('Bonus Type'),   
                'class' => 'awraf-action-type',
                'name' => 'type_' . AW_Raf_Model_Rule::ORDER_ITEM_QTY_TYPE,
                'options' => Mage::getSingleton('awraf/config_source_actionType')->toOptionArray('qty')
            ));  

            $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            $fieldset->addField('active_from', 'date', array(
                'name' => 'active_from',
                'label' => $this->__('Active From'),
                'title' => $this->__('Active From'),
                'required' => true,
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
                'format' => $outputFormat,
                'time' => true,
            ));        
        }      
        
        
        if($rule->getType() == AW_Raf_Model_Rule::SIGNUP_TYPE || $rule->getType() == AW_Raf_Model_Rule::ORDER_ITEM_QTY_TYPE) {
           $note = null;
        }
        else {
            $note = $this->__('Enter amount in base website currency');
        }
        
        $fieldset->addField('target', 'text', array(
            'label' => $this->__('Target'),
            'title' => $this->__('Target'),
            'required' => true,
            'class' => 'validate-greater-than-zero',
            'note' => $note,
            'name' => 'target'                
        ));
        
        $fieldset->addField('action', 'text', array(
            'label' => $this->__('Bonus Amount'),
            'title' => $this->__('Bonus Amount'),
            'required' => true,
            'class' => 'validate-greater-than-zero',
            'note' => $this->__('Enter amount in base website currency'),
            'name' => 'action'                
        )); 
        
        $fieldset->addField('priority', 'text', array(
            'label' => $this->__('Rule Priority'),
            'title' => $this->__('Rule Priority'),
            'note'  => $this->__('Rules with greater priority are processed first'),
            'name' => 'priority'                
        ));  
        
       
        $fieldset->addField('stop_on_first', 'select', array(
            'label' => $this->__('Stop Further Rules Processing'),
            'title' => $this->__('Stop Further Rules Processing'),
            'name' => 'stop_on_first',
            'options' => array(
                '1' => $this->__('Yes'),
                '0' => $this->__('No'),
            ),
        ));        
        
        $fieldset->addField('status', 'select', array(
            'label' => $this->__('Status'),
            'title' => $this->__('Status'),
            'name' => 'status',
            'options' => array(
                '1' => $this->__('Enabled'),
                '0' => $this->__('Disabled'),
            ),
        ));
      
        if (Mage::app()->isSingleStoreMode()) {
            $rule->setStoreIds(0);
            $fieldset->addField('store_ids', 'hidden', array(
                'name' => 'store_ids[]'                
            ));
        } else {
            $fieldset->addField('store_ids', 'multiselect', array(
                'name' => 'store_ids[]',
                'label' => $this->__('Store view'),
                'title' => $this->__('Store view'),
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }

       $form->setValues($rule->getData());        
       if ($rule->getData('active_from') && $form->getElement('active_from')) {
            $form->getElement('active_from')->setValue(                  
                   Mage::app()->getLocale()->date($rule->getData('active_from'), Varien_Date::DATETIME_INTERNAL_FORMAT)
            );
        }
        
        
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
