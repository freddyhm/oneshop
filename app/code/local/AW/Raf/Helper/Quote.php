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


class AW_Raf_Helper_Quote extends AW_Raf_Helper_Data
{

    protected $_store;
    protected $_address;

    public function minTotalExeeded()
    {
        if(!$minTotal = $this->getConfig()->minTotal($this->_store)) {
            return true;
        }
        
        $total =  $this->getCartTotal(); 
        
        if ($total > $minTotal) {
            return true;
        }

        return false;
    }

    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    public function setAddress($address)
    {
        $this->_address = $address;
        return $this;
    }

    public function couponsAllowed()
    {
        $items = $this->_address->getAllItems();

        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getDiscountAmount()) {
                if (!$this->getConfig()->isAllowedWithCoupons($this->_store)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function shouldRender()
    {
        return $this->couponsAllowed() && $this->minTotalExeeded();
    }

    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function maxDiscount()
    {
        $maxDiscount = $this->getConfig()->maxDiscount($this->_store);

        if (!$maxDiscount) {
            return false;
        }
        if (!$total = $this->_address->getBaseGrandTotal()) {
            return false;
        }
       
        return ($total - $this->_address->getBaseShippingInclTax() - $this->_address->getBaseTaxAmount()) * $maxDiscount / 100;
    }

    public function applyOn($type = 'base')
    {
        if ($type == 'base') {
            return $this->_address->getBaseGrandTotal() -
                    $this->_address->getBaseShippingInclTax() -
                    $this->_address->getBaseTaxAmount();
        }

        return $this->_address->getGrandTotal() -
                $this->_address->getShippingInclTax() -
                $this->_address->getTaxAmount();
    }
     
    public function getQuoteAddress()
    {
        if (!$quote = Mage::getSingleton('checkout/session')->getQuote()) {
            return false;
        }
        if ($quote->isVirtual()) {
            return $quote->getBillingAddress();
        } else {
            return $quote->getShippingAddress();
        }
    }
    
    public function getCartTotal()
    {
        $collected = 0;       
        foreach(Mage::getSingleton('checkout/cart')->getItems() as $item) {             
            $collected += $item->getBaseRowTotal();
        }

        return $collected;
    }

}
