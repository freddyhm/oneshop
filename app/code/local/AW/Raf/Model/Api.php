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


class AW_Raf_Model_Api extends Mage_Core_Model_Abstract
{

    /**
     * Add transaction to customer
     * @param Varien_Object $obj
     * @return obj AW_Raf_Model_Rule_Action_Transaction
     */
    public function add($obj)
    {
        if (is_array($obj)) {
            $obj = new Varien_Object($obj);
        }
        if (!$obj instanceof Varien_Object) {
            throw new Exception('Invalid param must be an array or instance of varien object');
        }
        if ($obj->getTrigger() == AW_Raf_Model_Rule::TRANSACTION_TRIGGER) {
            return Mage::getModel('awraf/rule_action_transaction')->createFromObject($obj);
        } elseif ($obj->getTrigger() == AW_Raf_Model_Rule::DISCOUNT_TRIGGER) {
            return Mage::getModel('awraf/rule_action_discount')->createFromObject($obj);
        }

        return false;
    }

    public function saveInTransaction(array $objects)
    {
        $transaction = Mage::getModel('core/resource_transaction');
        foreach ($objects as $obj) {

            if (!$obj instanceof Mage_Core_Model_Abstract) {
                continue;
            }

            $transaction->addObject($obj);
        }

        return $transaction->save();
    }

    public function getReferral($customer, $website)
    {
        return Mage::getModel('awraf/referral')->getReferral(
                        new Varien_Object(array('customer_id' => $customer, 'website_id' => $website)));
    }

    public function getReferralByEmail($email, $website)
    {
        return Mage::getModel('awraf/referral')->getReferral(
                        new Varien_Object(array('email' => $email, 'website_id' => $website)));
    }

    public function createReferral($obj)
    {
        if (is_array($obj)) {
            $obj = new Varien_Object($obj);
        }
        if (!$obj instanceof Varien_Object) {
            throw new Exception('Invalid param must be an array or instance of varien object');
        }
        return Mage::getModel('awraf/referral')->create($obj);
    }

    public function getAvailableAmount($customer, $website)
    {
        return Mage::getModel('awraf/rule_action_transaction')->getAvailableAmount(
                        new Varien_Object(array('customer_id' => $customer, 'website_id' => $website)));
    }

    public function getAvailableDiscount($customer, $website)
    {
        return Mage::getModel('awraf/rule_action_discount')->getAvailableDiscount(
                        new Varien_Object(array('customer_id' => $customer, 'website_id' => $website)));
    }

}