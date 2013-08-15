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


class AW_Raf_Model_Activity extends Mage_Core_Model_Abstract
{

    const COOKIE_NAME = 'awraf_referrer';
    const COOKIE_REFERRAL = 'awraf_referral';    
    /* */
    const STATUS_GUEST = 0;    
    const STATUS_SIGNUP_NOT_CONFIRMED = 2;    
    const  STATUS_SIGNUP_CONFIRMED = 1;    
    const STATUS_DISABLED = 4;
    /* */
    const SIGNUP_NEW_VALID = 1;
    const SIGNUP_NEW_CONFIRM = 2;
    const SIGNUP_BACK_LINK = 3;   
    
    protected $_activities = array();

    protected function _construct()
    {
        parent::_construct();
        $this->_init('awraf/activity');
    }

    public function register(Varien_Object $activity)
    {
        $this->setActivity($activity);

        foreach ($activity->getTypes() as $type) {
            $activity->setType($type);
            switch ($type) {
                case AW_Raf_Model_Rule::SIGNUP_TYPE:
                    $this->_signupRegister();
                    break;
                case AW_Raf_Model_Rule::ORDER_AMOUNT_TYPE:
                    $this->_orderAmountRegister();
                    break;
                case AW_Raf_Model_Rule::ORDER_ITEM_QTY_TYPE:
                    $this->_orderQtyRegister();
                    break;
                default:
                    throw new Exception("Unknown activity type {$type}");
            }
        }

        foreach ($this->getActivities() as $activity) {
            Mage::getSingleton('awraf/statistics')->updateStatistics($activity);
        }

        return $this;
    }

    protected function _orderQtyRegister()
    {    
        $invoice = $this->getActivity()->getInvoice();
        
        $qty = null;
        foreach ($invoice->getAllItems() as $item) {   
             $orderItem = $item->getOrderItem();
             if ($orderItem->isDummy()) {
                 continue;
             }
        
            $qty += $item->getQty();
        }
         
        $referralObj = $this->_getReferrerFromInvoice($invoice);

        if (!$referralObj) {
            return $this;
        }

        $this->getActivity()
                ->setAmount($qty)
                ->setWebsiteId($invoice->getStore()->getWebsite()->getId())
                ->setRlId($referralObj->getId())
                ->setRrId($referralObj->getReferrerId())
                ->setRelatedObject($invoice->getId())
                ->setType($this->getActivity()->getType());

        $this->create($this->getActivity());
    }

    protected function _orderAmountRegister()
    {
        $invoice = $this->getActivity()->getInvoice();

        $store = $invoice->getStoreId();

        $priceOnly = (Mage::helper('awraf/config')->calculatePurchaseAmount($store) ==
                AW_Raf_Model_Config_Source_Calculate::ONLY_PRICE);

        $amount = null;
        foreach ($invoice->getAllItems() as $item) {
             $orderItem = $item->getOrderItem();
             if ($orderItem->isDummy()) {
                 continue;
             }
            if ($priceOnly) {
                $amount += $item->getBasePrice() * $item->getQty() - abs($item->getBaseDiscountAmount());
            } else {
                $amount += $item->getBasePriceInclTax() * $item->getQty() - abs($item->getBaseDiscountAmount());
            }
        }

        $referralObj = $this->_getReferrerFromInvoice($invoice);

        if (!$referralObj) {
            return $this;
        }

        $this->getActivity()
                ->setAmount($amount)
                ->setWebsiteId($invoice->getStore()->getWebsite()->getId())
                ->setRlId($referralObj->getId())
                ->setRrId($referralObj->getReferrerId())
                ->setRelatedObject($invoice->getId())
                ->setType($this->getActivity()->getType());

        $this->create($this->getActivity());
    }

    protected function _getReferrerFromInvoice($invoice)
    {       
       $orderByRef = Mage::getModel('awraf/orderref')->load($invoice->getOrder()->getIncrementId(), 'order_increment');   
        
       if($orderByRef->getId() && $orderByRef->getCustomerId()) {            
           /* quest purchased by link */
           if(is_null($orderByRef->getReferralId())) {          
               return new Varien_Object(array('referrer_id' => $orderByRef->getCustomerId()));
           }           
           $refObject = Mage::getModel('awraf/referral')->load($orderByRef->getCustomerId(), 'customer_id');           
           if ($refObject->getId()) {
                return $refObject;
           }           
       }      
    }

    protected function _signupRegister()
    {      
        $activity = $this->getActivity();

        $customer = $activity->getCustomer();
        
        $store = Mage::app()->getStore($customer->getStoreId());        
        /**
         *  $customer->getSaveRafStatus
         *  1 - new customer: create referral, activity and give bonus
         *  2 - new customer not confirmed: create referral with status not confirmed
         *  3 - gets back by confirmation link: generate activity and give bonus, activate referral status
         */         
        $status = self::STATUS_SIGNUP_CONFIRMED;
        if($customer->getConfirmation()) {
            $status = self::STATUS_SIGNUP_NOT_CONFIRMED;
        }       
        /* process referral object only if it is not back confirmation link */
        if($customer->getSaveRafStatus() != self::SIGNUP_BACK_LINK) {
            if ($activity->getReferral()) {
                $referralObj = Mage::getModel('awraf/referral')->load($activity->getReferral());
                if (!$referralObj->getId()) {
                    /* invalidated or expired link */
                    return $this;
                }
                $referralObj->setReferrerId($activity->getReferrer())
                        ->setEmail($customer->getEmail())
                        ->setCustomerId($customer->getId())
                        ->setWebsiteId($store->getWebsite()->getId())
                        ->setStoreId($store->getId())
                        ->setStatus($status)
                        ->save();

                Mage::helper('awraf')->unsReferral();            

            } else {              
                $referral = new Varien_Object();
                $referral->setReferrerId($activity->getReferrer())
                        ->setEmail($customer->getEmail())
                        ->setCustomerId($customer->getId())
                        ->setWebsiteId($store->getWebsite()->getId())
                        ->setStoreId($store->getId())
                        ->setStatus($status);                
                /* first of all check if customer with such email already invited */
                $referralObj = Mage::getModel('awraf/api')->getReferralByEmail($referral->getEmail(), $referral->getWebsiteId());                
                if(!$referralObj->getId()) {
                     $referralObj = Mage::getModel('awraf/api')->createReferral($referral);
                }
                else {
                   $referralObj->setCustomerId($referral->getCustomerId())->setStatus($status)->save();
                }
            }
        }
        /* activate referral */
        if($customer->getSaveRafStatus() == self::SIGNUP_BACK_LINK) {  
           $referralObj = Mage::getModel('awraf/referral')->load($customer->getId(), 'customer_id');           
           if(!$referralObj->getId()) {
               return $this;
           }            
           $referralObj->setStatus(self::STATUS_SIGNUP_CONFIRMED)->save();         
        }         
        /* create activity object */
        if(!$customer->getConfirmation()) {
            $this->getActivity()
                    ->setAmount(1)
                    ->setWebsiteId($store->getWebsite()->getId())
                    ->setRlId($referralObj->getId())
                    ->setRrId($activity->getReferrer())
                    ->setType($activity->getType());

            $this->create($this->getActivity());        

            $this->_addBonusToReferral($referralObj);
         }
    }

    protected function _addBonusToReferral($referral)
    {
        $configHelper = Mage::helper('awraf/config');
        $bonusEnabled = $configHelper->isBonusToReferral($referral->getStoreId());
        $bonusType = $configHelper->referralBonusType($referral->getStoreId());
        $bonusAmount = $configHelper->referralDiscount($referral->getStoreId());
        $transport = new Varien_Object();

        if ($bonusEnabled && $bonusAmount) {
            $transport
                    ->setCustomerId($referral->getCustomerId())
                    ->setWebsiteId($referral->getWebsiteId())
                    ->setComment(Mage::helper('awraf')->autoMessage(AW_Raf_Model_Rule::BONUS_REGISTER))
                    ->setDiscount($bonusAmount);

            $transport->setTrigger(AW_Raf_Model_Rule::TRANSACTION_TRIGGER);
            if ($bonusType == AW_Raf_Model_Rule::PERCENT_TYPE) {
                $transport->setTrigger(AW_Raf_Model_Rule::DISCOUNT_TRIGGER);
                $transport->setType($bonusType);
            }

            $discountObject = Mage::getModel('awraf/api')->add($transport);

            if (!$discountObject) {
                throw new Exception('Failed to give discount to referral');
            }
            
           $params =  array('referral' => $referral, 'discount' => $discountObject);
           Mage::dispatchEvent('aw_raf_referral_discount_add_after', $params);        
            
        }
    }

    public function getActivityByType($type)
    {
        if (isset($this->_activities[$type])) {
            return $this->_activities[$type];
        }

        return false;
    }

    public function getActivities()
    {
        return $this->_activities;
    }

    public function create(Varien_Object $activity = null)
    {
        if (!$activity) {
            $activity = $this->getActivity();
        }
        if (!$activity instanceof Varien_Object) {
            throw new Exception('Activity is not an object');
        }

        $instance = new self();

        $instance->setData($activity->getData())
                ->setCreatedAt(Mage::getModel('core/date')->gmtDate())
                ->save();
                
        Mage::dispatchEvent('aw_raf_activity_create_after', array('activity' => $activity));        

        $this->_activities[$instance->getType()] = $instance;

        return $this;
    }

}