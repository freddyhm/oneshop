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


class AW_Raf_Model_Observer
{

    protected static $_addressChanged = array();

    public function customerSaveBefore($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        
        if($this->moduleDisabled($customer->getStoreId()))
             return;

        if (!$customer->getId()) {
            return $this;
        }
        if (!$customer->getOrigData()) {
            $email = Mage::getModel('customer/customer')->load($customer->getId())->getEmail();
        } else {
            $email = $customer->getOrigData('email');
        }
        /* customer changed email address 
         * process event only in after save to avoide save errors 
         */
        if (trim($email) != trim($customer->getEmail())) {
            self::$_addressChanged[$customer->getId()] = true;
        }
    }

    /**
     * Signup activity if referrer cookie is set
     * @param type $observer
     */
    public function customerSaveAfter($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        
        if($this->moduleDisabled($customer->getStoreId()))
             return;
        
        /* change referral email if customer changed address info */
        if (isset(self::$_addressChanged[$customer->getId()])) {
            $referral = Mage::getModel('awraf/referral')->load($customer->getId(), 'customer_id');
            if ($referral->getId()) {
                $referral->setEmail($customer->getEmail())->save();
            }
        }
        /* customer gets back by confirmation link */
        if ($customer->getOrigData('confirmation') && !$customer->getConfirmation()) {
            $customer->setSaveRafStatus(AW_Raf_Model_Activity::SIGNUP_BACK_LINK);
        }
        /* new registered customer confirmation not required */ 
        elseif ($this->isNew($customer) && (Mage::getSingleton('customer/session')->isLoggedIn() || 
                (Mage::app()->getRequest()->getModuleName() == 'checkout'))) {
            $customer->setSaveRafStatus(AW_Raf_Model_Activity::SIGNUP_NEW_VALID);
        }
        /* new registered customer confirmation required */ 
        elseif ($this->isNew($customer) && $this->confirmationRequired($customer)) {
            $customer->setSaveRafStatus(AW_Raf_Model_Activity::SIGNUP_NEW_CONFIRM);
        }

        if (is_null($customer->getSaveRafStatus())) {
            return;
        }

        /* if there is no referrer cookie and it is not back confirmation */
        if ((!$referrer = $this->getReferrer()) && ($customer->getSaveRafStatus() != AW_Raf_Model_Activity::SIGNUP_BACK_LINK)) {
            return;
        }

        $transport = $this->_transport()
                ->setCustomer($customer)
                ->setStoreId(Mage::app()->getStore()->getId())
                ->setTypes(array(AW_Raf_Model_Rule::SIGNUP_TYPE))
                ->setReferrer($referrer)
                ->setReferral($this->getReferral());

        $this->_processor()->process($transport);
    }

    public function confirmationRequired($customer)
    {
        return Mage::helper('awraf')->getConfig()->confirmationRequired($customer->getStoreId());
    }

    public function isNew($customer)
    {
        return $customer->getUpdatedAt() == $customer->getCreatedAt();
    }

    public function quoteSaveAfter($observer)
    {
        if($this->moduleDisabled())
             return;
        
        if (!$observer->getQuote()->hasItems()) {
            Mage::helper('awraf')->clearAppliedAmount();
        }
    }

    public function coreBlockAbstractToHtmlAfter($observer)
    {
        if ($observer->getBlock() instanceof Mage_Checkout_Block_Cart_Coupon) {
            
            if($this->moduleDisabled())
                    return;

            $block = Mage::app()->getLayout()->createBlock('awraf/checkout_cart_discount');

            $observer->getTransport()->setHtml($observer->getTransport()->getHtml() . $block->toHtml());
        }
    }

    public function frontControllerPredispatch($observer)
    {      
        if($this->moduleDisabled())
            return;
        
        $helper = Mage::helper('awraf');

        $rel = Mage::app()->getRequest()->getParam('rel', false);
        if ($rel) {
            $key = (int) $helper->decodeUrlKey($rel);
            if ($key) {
                $helper->setReferral($key);
            }
        }

        $ref = Mage::app()->getRequest()->getParam('ref', false);
        if ($ref) {
            $key = (int) $helper->decodeUrlKey($ref);
            if ($key) {
                $helper->setReferrer($key);
                $redirectUrl = $helper->getConfig()->getRedirectTo(Mage::app()->getStore()->getId());
                if ($redirectUrl) {
                    Mage::app()->getResponse()->setRedirect($redirectUrl)->sendResponse();
                    exit();
                }
            }
        }
    }

    public function generateBlocksAfter($observer)
    {       
        $block = $observer->getBlock();

        $helper = Mage::helper('awraf');

        if ($block->getNameInLayout() == 'invoice_totals') {
            
            $customer = Mage::getModel('customer/customer')->load($block->getSource()->getOrder()->getCustomerId());
            if (!$customer->getId()) {
                return;
            }
            if ((float) $block->getSource()->getAwrafs()) {
                $block->addTotal(new Varien_Object(array(
                            'code' => 'awraf',
                            'value' => $block->getSource()->getAwrafs(),
                            'base_value' => $block->getSource()->getAwrafsBase(),
                            'label' => $helper->__('Applied Discount For Referred Friends')
                        )), 'subtotal');

                $observer->getTransport()->setHtml($block->renderView());
            }
        }

        if ($block->getNameInLayout() == 'order_totals') {
           
            $customer = Mage::getModel('customer/customer')->load($block->getOrder()->getCustomerId());
            if (!$customer->getId()) {
                return;
            }
            $baseDiscount = Mage::getModel('awraf/orderref')->getTotalDiscount($block->getOrder());
            
            if(!(float) $baseDiscount) {
                return;
            }
            
            $storeDiscount = $helper->convertAmount(-1.00 * $baseDiscount, 
                    array('store' => $block->getOrder()->getStore(), 
                        'direction' => AW_Raf_Helper_Data::CONVERT_TO_CURRENT));

            $block->addTotal(new Varien_Object(array(
                        'code' => 'awraf',
                        'value' => -$storeDiscount,
                        'base_value' => $baseDiscount,
                        'label' => $helper->__('Applied Discount For Referred Friends')
                    )), 'subtotal');

            $observer->getTransport()->setHtml($block->renderView());
        }
    }

    public function moduleDisabled($store = null)
    {
        if (null === $store) {
            $store = Mage::app()->getStore()->getId();
        }

        return Mage::helper('awraf')->getConfig()->moduleDisabled($store);
    }

    public function invoiceLoadAfter($observer)
    {        
        Mage::getModel('awraf/total_invoice_rafs')->collect($observer->getInvoice()->setAwrafWithoutGrandTotal(true));
    }

    public function paypalPrepare($observer)
    {        
         if($this->moduleDisabled())
               return;
        
        $hlp = Mage::helper('awraf');
        $paypalCart = $observer->getEvent()->getPaypalCart();
        $appliedMoney = $hlp->getAppliedDiscount() + $hlp->getAppliedAmount();

        if ($paypalCart && $appliedMoney) {            
            $paypalCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_DISCOUNT, $appliedMoney, $hlp->__('Discount for referred friends'));
        }
    }

    /**
     * Order events
     * @param type $observer
     */
    public function orderPlaceAfter($observer)
    {       
        $helper = Mage::helper('awraf');

        $order = $observer->getOrder();
       
        if($this->moduleDisabled($order->getStoreId()))
               return;
        /* get applied amounts from session */
        $applied = $helper->getOrder()->getAppliedRafAmounts($order);
       
        $websiteId = $order->getStore()->getWebsite()->getId();
         
        $orderInfo = new Varien_Object(array(
            'applied_amount' => $applied['amount'],
            'applied_discount' => $applied['discount']
        ));
        
        $referralInfo = $helper->getOrder()->getReferralInfo($order, $orderInfo);
        
        $info = Mage::getModel('awraf/orderref')
                ->setOrderIncrement($order->getIncrementId())
                ->setCustomerId($referralInfo['customer'])
                ->setReferralId($referralInfo['referral']->getId())
                ->setWebsiteId($websiteId)
                ->setCreatedAt(Mage::getModel('core/date')->gmtDate())
                ->setOrderInfo($orderInfo->toJSON())
                ->save();
       
        $customer = $helper->getCustomer()->getId();
        if ($info->getId() && $applied['amount'] && $customer) {

            $transport = new Varien_Object();
            $transport
                    ->setCustomerId($customer)
                    ->setWebsiteId($websiteId)
                    ->setComment(Mage::helper('awraf')->autoMessage(AW_Raf_Model_Rule::ORDER_FEE))
                    ->setDiscount(-$applied['amount'])
                    ->setTrigger(AW_Raf_Model_Rule::TRANSACTION_TRIGGER);

            Mage::getModel('awraf/api')->add($transport);
 
            Mage::getSingleton('awraf/statistics')->updateStatistics(new Varien_Object(array(
                        'rr_id' => $customer,
                        'spent' => $applied['amount']
            )));
        }
       
        if ($applied['discount'] && $referralInfo['discount_obj']) {
            $referralInfo['discount_obj']->updateTriggerCount(1);
        }
      
    }

    /**
     * Order amount activity
     * Order qty activity
     * @param type $observer
     */
    public function invoicePay($observer)
    { 
        
        $orderByRef = Mage::getModel('awraf/orderref')
                ->load($observer->getInvoice()->getOrder()->getIncrementId(), 'order_increment');

        if (!$orderByRef->getId()) {

            $emulation = new Varien_Object(array('order' => $observer->getInvoice()->getOrder()));

            $this->orderPlaceAfter($emulation);
        }
        
       if($this->moduleDisabled($observer->getInvoice()->getStoreId()))
               return;
        
        $transport = $this->_transport()
                ->setInvoice($observer->getInvoice())
                ->setStoreId($observer->getInvoice()->getStoreId())
                ->setTypes(array(AW_Raf_Model_Rule::ORDER_AMOUNT_TYPE, AW_Raf_Model_Rule::ORDER_ITEM_QTY_TYPE));

        $this->_processor()->process($transport);
         
    }

    public function getReferrer()
    {
        return Mage::helper('awraf')->getReferrer();
    }

    public function getReferral()
    {
        return Mage::helper('awraf')->getReferral();
    }

    protected function _processor()
    {
        return Mage::getModel('awraf/processor');
    }

    protected function _transport()
    {
        return new Varien_Object();
    }

}