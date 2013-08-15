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


class AW_Raf_Block_Adminhtml_Bonus_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'awraf';
        $this->_controller = 'adminhtml_bonus';

        $this->_formScripts[] = "    
            
 
                document.observe('dom:loaded', function() { 
                   try {                   
                       $('customer_fieldset_massaction-select').up('div.right').remove();
                    } catch(e) { }                   
                });
 
                    function saveAndContinueEdit(url) {                      
                        if(typeof customer_fieldset_massactionJsObject != 'undefined') {                             
                            $('selected_values').value = customer_fieldset_massactionJsObject.getCheckedValues(); 
                        }      
                    
                        editForm.submit(url);
                    } 
                    
                    function saveRafDiscount() {  
                        
                        if(typeof customer_fieldset_massactionJsObject != 'undefined') {                             
                            $('selected_values').value = customer_fieldset_massactionJsObject.getCheckedValues(); 
                        }   
                       
                        editForm.submit();
                    } 
                ";

        parent::__construct();
    }

    public function getHeaderText()
    {
        $rule = Mage::registry('awraf_rule');
        if ($rule->getId()) {
            if ($rule->getRuleName()) {
                return $this->__("Edit Discount '%s'", $this->htmlEscape($rule->getRuleName()));
            }
            return $this->__("Edit Discount #'%s'", $this->htmlEscape($rule->getId()));
        } else {
            return $this->__('Create Discount');
        }
    }

    protected function _prepareLayout()
    {
        $rule = Mage::registry('awraf_rule');

        parent::_prepareLayout();

        if ($rule->getId()) {
            $this->_addButton('save_and_continue', array(
                'label' => $this->__('Save and Continue Edit'),
                'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl() . '\')',
                'class' => 'save'
                    ), 10);
        } else {
            
            $this->_removeButton('save');


            $this->_addButton('add_discount', array(
                'label' => $this->__('Add Discount'),
                'onclick' => 'saveRafDiscount()',
                'class' => 'save'
                    ), 10);
        }
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
                    '_current' => true,
                    'back' => 'edit',
                    'tab' => '{{tab_id}}'
                ));
    }

}
