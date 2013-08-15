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


class AW_Raf_CartController extends Mage_Core_Controller_Front_Action
{

    public function createCouponAction()
    {
        $helper = Mage::helper('awraf');
        
        $bcmath = $helper->getMath();

        $customer = $helper->getCustomer();

        if (!$customer->getId()) {
            return $this->msg('Only logged in customers can apply discounts', 'error');
        }

        $availableAmount = Mage::getModel('awraf/api')
                ->getAvailableAmount($customer->getId(), Mage::app()->getWebsite()->getId());
        
        $post = Mage::app()->getRequest()->getPost();

        if (!isset($post['remove'])) {
            return $this->msg('Incorrect post data');
        }
        if ($post['remove']) {
            $helper->clearAppliedAmount();
            $helper->setReservedAmount(0);
            return $this->msg('Discount has been cancelled', 'success');
        }
        if (!isset($post['awraf-amount'])) {
            return $this->msg('Incorrect post data');
        }
        
        $params = array(
            'store' => Mage::app()->getStore(),
            'format' => null,
            'floor' => true,
            'direction' => AW_Raf_Helper_Data::CONVERT_TO_BASE);
    
        $amount = Mage::helper('awraf')->convertAmount($post['awraf-amount'], $params);
         
        if (!$amount) {

            $minimalAmount = Mage::helper('awraf')->convertAmount(0.01, array(
                'store' => Mage::app()->getStore(),
                'format' => true,
                'direction' => AW_Raf_Helper_Data::CONVERT_TO_CURRENT));

            return $this->msg('Minimal amount to apply is %s', $minimalAmount);
        }
        
        $margin = $bcmath->sub($availableAmount, $helper->getAppliedAmount());        
        if ($bcmath->comp($amount, $margin) == 1) {
            return $this->msg('Not enough money to apply');
        }
        
        $currentRate = Mage::helper('awraf')->getCurrentRate(Mage::app()->getStore());
        if($bcmath->comp(1, $currentRate) == 1) {             
            unset($params['floor']);
            $helper->setReservedAmount(Mage::helper('awraf')->convertAmount($post['awraf-amount'], $params)); 
        }
        else {
            $helper->setReservedAmount($amount); 
        }
       
        return $this->_redirectReferer();
    }

    public function msg($msg, $params = array(), $type = 'error')
    {        
        $params = (array) $params;
        array_unshift($params, $msg);
        $msg = call_user_func_array(array($this, '__'), $params);         
        $method = 'add' . ucfirst($type);
        Mage::getSingleton('checkout/session')->{$method}($msg);
        return $this->_redirectReferer();
    }

}