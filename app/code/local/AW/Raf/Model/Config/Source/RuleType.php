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




class AW_Raf_Model_Config_Source_RuleType
{

    public function toOptionArray($type = null)
    {
        $options = array(
            AW_Raf_Model_Rule::SIGNUP_TYPE => Mage::helper('awraf')->__('Store sign up'),
            AW_Raf_Model_Rule::ORDER_ITEM_QTY_TYPE => Mage::helper('awraf')->__('Discount for a number of items'),
            AW_Raf_Model_Rule::ORDER_AMOUNT_TYPE => Mage::helper('awraf')->__("Discount for referrals' purchase amount")
        );

        if ($type !== null) {
            if (isset($options[$type])) {
                return $options[$type];
            }
            return false;
        }

        return $options;
    }

    public function getAutoMessage($type = null)
    {        
        $helper = Mage::helper('awraf');
        
        $messages = array(
            AW_Raf_Model_Rule::BONUS_REGISTER => $helper->__('Auto message: Bonus for registration'),
            AW_Raf_Model_Rule::SIGNUP_TYPE => $helper->__('Auto message: Bonus for referral signups'),
            AW_Raf_Model_Rule::ORDER_ITEM_QTY_TYPE => $helper->__('Auto message: Bonus for the number of items purchased by referrals'),
            AW_Raf_Model_Rule::ORDER_AMOUNT_TYPE => $helper->__('Auto message: Bonus for the amount of money spent by referrals'),
            AW_Raf_Model_Rule::ORDER_FEE => $helper->__('Auto message: Bonus for referred friends spent on order')
        );

        if ($type !== null) {
            if (isset($messages[$type])) {               
                 return $messages[$type];            
            }
            return false;
        }

        return $messages;
    }

    public function toLabel($type)
    {
        return $this->toOptionArray($type);
    }

}