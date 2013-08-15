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


class AW_Raf_Helper_Order extends AW_Raf_Helper_Data
{

    public function getAppliedRafAmounts($order)
    {
        $quoteAddressId = null;
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $quoteAddressId = Mage::getModel('sales/quote_address_item')
                            ->load($item->getQuoteItemId())->getQuoteAddressId();
            if (!$quoteAddressId) {
                $appliedAmount = $this->getAppliedAmount();
                $appliedDiscount = $this->getAppliedDiscount();
            } else {
                $appliedAmount = $this->getDiscountByType('amount', $quoteAddressId);
                $appliedDiscount = $this->getDiscountByType('discount', $quoteAddressId);
            }
            break;
        }

        return array(
            'amount' => $appliedAmount,
            'discount' => $appliedDiscount,
            'quote_address' => $quoteAddressId
        );
    }

    public function getReferralInfo($order, $orderInfo)
    { 
        $customerId = $order->getCustomerId();

        $websiteId = $order->getStore()->getWebsite()->getId();

        $selfCustomer = false;
        $discountObject = false;
        if ($customerId) {
            $ref = $this->getApi()->getReferral($customerId, $websiteId);
            $discountObject = $this->getApi()->getAvailableDiscount($customerId, $websiteId);
            $orderInfo->setAppliedDiscountInfo($discountObject->toArray());
            if (!$ref->getId()) {
                $selfCustomer = true;
                $customerId = null;
            }
        } else {
            $ref = Mage::getModel('awraf/referral')->load($this->getReferral());
            $customerId = $ref->getReferrer();
        }
        if (!$customerId && !$selfCustomer) {
            $customerId = $this->getReferrer();
        }
        
        return array(
            'customer' => $customerId,
            'referral' => $ref,
            'discount_obj' => $discountObject
        );       
    }

    public function clearAll(array $applied)
    {
        $this->clearDiscountByType('amount', @$applied['quote_address']);
        $this->clearDiscountByType('discount', @$applied['quote_address']);
        $this->clearAppliedAmount();
        $this->clearAppliedDiscount();
    }

}
