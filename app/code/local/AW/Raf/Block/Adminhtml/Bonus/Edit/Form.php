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


class AW_Raf_Block_Adminhtml_Bonus_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('id');
        $this->setTitle($this->__('Rule Information'));
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form',
                    'action' => Mage::getUrl('*/*/save', array('id' => Mage::app()->getRequest()->getParam('id'))), 'method' => 'post', 'enctype' => 'multipart/form-data'));
        $form->setUseContainer(true);

        $rule = Mage::registry('awraf_rule');
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $this->__('Discount Details')));


        if ($rule->getId()) {
                /*
            $fieldset->addField('trig_qty', 'label', array(
                'label' => $this->__('Trigger Count'),
                'title' => $this->__('Trigger Count'),
                'name' => 'trig_qty'
            ));
            $fieldset->addField('created_at', 'label', array(
                'name' => 'created_at',
                'label' => $this->__('Created At'),
                'title' => $this->__('Created At')
            ));*/
            if ($rule->getRuleId()) {
                $ruleObj = Mage::getModel('awraf/rule')->load($rule->getRuleId());
                if ($ruleObj->getId()) {                  
                    $fieldset->addField('rule_name', 'label', array(
                        'label' => $this->__('Related Rule')                       
                    ))
                      ->setRule($ruleObj)
                      ->setRenderer($this->getLayout()->createBlock('awraf/adminhtml_edit_renderer_ruleName'));
                }
            }            
        }
        else {
             
            $fieldset->addField('selected_values', 'hidden', array(          
                'name' => 'selected_values'         
            ));    
        }
        
        if($rule->getCustomerId()) {
            $customer = Mage::getModel('customer/customer')->load($rule->getCustomerId());
            if($customer->getId()) {                
               $fieldset->addField('customer_name', 'label', array(
                    'label' => $this->__('Customer')
               ))  ->setCustomer($customer)
                   ->setRenderer($this->getLayout()->createBlock('awraf/adminhtml_edit_renderer_customerName'));
            }         
        }
       /*
        $fieldset->addField('type', 'select', array(
            'label' => $this->__('Discount Type'),
            'title' => $this->__('Discount Type'),
            'name' => 'type',
            'options' => Mage::getSingleton('awraf/config_source_actionType')->toOptionArray()
        ));*/
        
      

        $fieldset->addField('discount', 'text', array(
            'label' => $this->__('Discount Amount %'),
            'title' => $this->__('Discount Amount %'),
            'required' => true,
            'class' => 'validate-greater-than-zero',          
            'name' => 'discount'
        ));
        
         $fieldset->addField('comment', 'text', array(
            'label' => $this->__('Comment'),
            'title' => $this->__('Comment'),                       
            'name' => 'comment'
        ));
        
        if (Mage::app()->isSingleStoreMode()) {
            $websiteId = Mage::app()->getStore(true)->getWebsiteId();
            $fieldset->addField('website_id', 'hidden', array(
                'name'     => 'raf_website',
                'value'    => $websiteId
            ));
            $rule->setWebsiteId($websiteId);
        } else {
            $fieldset->addField('website_id', 'select', array(
                'name'     => 'raf_website',
                'label'     => $this->__('Website'),
                'title'     => $this->__('Website'),                 
                'values'   => Mage::getSingleton('adminhtml/system_config_source_website')->toOptionArray()
            ));       
        }

        $form->setValues($rule->getData());
        if ($rule->getData('created_at') && $form->getElement('created_at')) {
            $form->getElement('created_at')->setValue(
                    Mage::app()->getLocale()->date($rule->getData('created_at'), Varien_Date::DATETIME_INTERNAL_FORMAT)
            );
        }

        if(!$rule->getCustomerId()) {
            $customerFieldset = $form->addFieldset('customer_fieldset', array('legend' => $this->__('Customers')));
            $customerFieldset->addField('customer_grid', 'text', array(
                'label' => $this->__('Customer Grid')                 
            ))->setRenderer($this->getLayout()->createBlock('awraf/adminhtml_edit_renderer_customerGrid'));           
        }
       
        if($formData = Mage::getSingleton('adminhtml/session')->getFormData(true)) {            
              $form->setValues($formData);
        }
        else {
             $form->setValues($rule->getData()); 
        }

      

        $this->setForm($form);
        return parent::_prepareForm();
    }


}
