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


class AW_Raf_IndexController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->_session()->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    public function statsAction()
    {
        $this->loadLayout();

        $this->_initMessages();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Referred Friends'));
        $block = $this->getLayout()->getBlock('awraf.account.link.back');
        if ($block) {
            $block->setRefererUrl($this->_getRefererUrl());
        }

        $this->renderLayout();
    }

    protected function _initMessages()
    {
        $this->_initLayoutMessages('customer/session');
    }

    protected function _session()
    {
        return Mage::getSingleton('customer/session');
    }

    public function inviteAction()
    {
        $this->_initMessages();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('awraf/invite')->addData((array) $this->_session()->getFormData())->toHtml()
        );
    }

    public function validate(Varien_Object $transport)
    {
        if (!Zend_Validate::is(trim($transport->getEmail()), 'NotEmpty')) {
            throw new Exception($this->__('Email field should not be empty'));
        }
        if (!Zend_Validate::is(trim($transport->getSubject()), 'NotEmpty')) {
            throw new Exception($this->__('Subject field should not be empty'));
        }
        if (!Zend_Validate::is(trim($transport->getMessage()), 'NotEmpty')) {
            throw new Exception($this->__('Message should not be empty'));
        }        
        
        $emails = array_unique(preg_split('/[\s;]/', $transport->getEmail(), -1, PREG_SPLIT_NO_EMPTY));          
      
        if(empty($emails)) {
             throw new Exception($this->__('Please enter at least on email address'));            
        }        
        if (count($emails) > AW_Raf_Helper_Config::MAX_EMAILS_PER_LAUNCH) {
            throw new Exception($this->__('Maximum number of emails per launch has been exceeded'));
        }
        foreach ($emails as $email) {
            if (!Zend_Validate::is(trim($email), 'EmailAddress')) {
                throw new Exception($this->__("Invalid email address %s", $email));
            }
        }

        $transport->setEmail($emails);
    }

    public function inviteSendAction()
    {
        $helper = Mage::helper('awraf');
        if (!Mage::app()->getRequest()->isPost()) {
            throw new Exception('Bad Request');
        }

        $transport = new Varien_Object($this->getRequest()->getParam('invite', array()));

        try {
            $this->validate($transport);
        } catch (Exception $e) {
            $this->_session()->setFormData($transport->getData());
            $this->_session()->addError($helper->__($e->getMessage()));
            return $this->_forward('invite');
        }
 
        $duplicates = $this->_getEmailDuplicates($transport->getEmail());
         
        $registered = $this->_getRegisteredCustomers($transport->getEmail());       
        
        $store = Mage::app()->getStore();
        foreach ($transport->getEmail() as $email) {            
            /* first check for duplicates in registered customers */
            foreach($registered as $register) {                
                if($register->getEmail() == trim($email)) {
                    $this->_session()->addError($helper->__('Email %s already a registered customer', $email));
                    continue 2;           
                }
            }           
           
            $referral = $helper->getApi()->createReferral(array(
                'referrer_id' => $helper->getCustomer()->getId(),
                'website_id' => $store->getWebsite()->getId(),
                'store_id' => $store->getId(),
                'email' => trim($email)
                    ));

            $transport->setInviteLink($helper->getReferrerLink($referral->getReferrerId(), $referral->getId()))
                    ->setReferrerName($helper->getCustomer()->getName())
                    ->setEmailLaunch($email)
                    ->setStoreId($store->getId())
                    ->setParams(array('invitation' => $transport));

            try {
                $error = false;
                $helper->getNotification()->send($transport);                  
                /* invalidate old invitations to the same address */
                foreach($duplicates as $duplicate) {
                   if($duplicate->getEmail() == trim($email)) {
                       $duplicate->delete();
                   }
                }
                $this->_session()->addSuccess($helper->__('Email to %s has been successfully sent', $email));
            } catch (Excetpion $e) {
                $error = true;
                Mage::logException($e);
                $referral->delete();
                $this->_session()->addError($helper->__('Error on sending email notifications. Please contact store administrator'));
            }
             
            if(!$error) {
                $this->_session()->unsFormData();
            }
        }
        
        return $this->_forward('invite');
    }
    
    protected function _getEmailDuplicates($emails)
    {
        $referralInfo = array(
            'referrer_id' => Mage::helper('awraf')->getCustomer()->getId(),
            'email' => $emails,
            'website_id' => Mage::app()->getStore()->getWebsite()->getId()
        );

        return Mage::getModel('awraf/referral')->getCollection()
                        ->getReferral(new Varien_Object($referralInfo))
                        ->addFieldToFilter('customer_id', array('null' => true))
                        ->load();
    }

    protected function _getRegisteredCustomers($emails)
    {
        $customers = Mage::getModel('customer/customer')->getCollection()
                ->addFieldToFilter('email', array('in' => $emails));

        if (Mage::getStoreConfig('customer/account_share/scope')) {
            $customers->addFieldToFilter('website_id', array('eq' => Mage::app()->getWebsite()->getId()));
        }

        return $customers->load();
    }

}