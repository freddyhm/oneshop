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


class AW_Raf_Helper_Config extends Mage_Core_Helper_Abstract
{

    const MAX_EMAILS_PER_LAUNCH = 5;

    public function calculatePurchaseAmount($store)
    {
        return Mage::getStoreConfig('awraf/general/calculate', $store);
    }

    public function isAllowedWithCoupons($store)
    {
        return Mage::getStoreConfig('awraf/general/coupons', $store);
    }

    public function minTotal($store)
    {
        return (float) Mage::getStoreConfig('awraf/general/cart_total', $store);
    }

    public function maxDiscount($store)
    {
        return (int) Mage::getStoreConfig('awraf/general/max_limit', $store);
    }

    public function minDiscount($store)
    {
        return (float) Mage::getStoreConfig('awraf/general/min_limit', $store);
    }

    public function isBonusToReferral($store)
    {
        return Mage::getStoreConfig('awraf/referral/bonus', $store);
    }

    public function referralBonusType($store)
    {
        return Mage::getStoreConfig('awraf/referral/bonus_type', $store);
    }

    public function referralDiscount($store)
    {
        return (float) Mage::getStoreConfig('awraf/referral/bonus_amount', $store);
    }

    /* Invitation params */

    public function getRedirectTo($store)
    {
        return trim(Mage::getStoreConfig('awraf/invite/redirect_link', $store));
    }

    public function isInviteAllowed($store)
    {
        return Mage::getStoreConfig('awraf/invite/enabled', $store);
    }

    public function confirmationRequired($store)
    {
        return Mage::getStoreConfig('customer/create_account/confirm', $store);
    }

    public function getNotificationTemplate($store)
    {
        return Mage::getStoreConfig('awraf/invite/template', $store);
    }

    public function getEmailSender($store)
    {
        return Mage::getStoreConfig('awraf/invite/email_sender', $store);
    }

    public function moduleDisabled($store)
    {
        return Mage::getStoreConfig('advanced/modules_disable_output/AW_Raf', $store);
    }

    public function getSenderData($store)
    {
        $sender = $this->getEmailSender($store);
        return array(
            'name' => Mage::getStoreConfig("trans_email/ident_{$sender}/name", $store),
            'email' => Mage::getStoreConfig("trans_email/ident_{$sender}/email", $store)
        );
    }

}
