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


class AW_Raf_Model_Rule_Validator_Abstract extends Mage_Core_Model_Abstract
{
    protected function _prepareRuleTrigger($rule)
    {
        $trigger = Mage::getModel('awraf/trigger');
        
        $result = $trigger->getCollection()
                ->getConnection()
                ->query("SELECT `t`.*, SUM(trig_qty) AS `limit`, MAX(created_at) AS `active_from` FROM 
                        (SELECT * FROM `{$trigger->getResource()->getMainTable()}` ORDER BY `created_at` DESC) AS `t` 
                        WHERE (`customer_id` = {$rule->getActivity()->getRrId()}) AND (`rule_id` = {$rule->getId()})"
                )
                ->fetch();
 
        if (is_array($result) && isset($result['item_id'])) {
            $trigger->addData($result);
        }

       return $rule->setTrigger($trigger);
    }

    protected function _prepareActivityAmount($rule)
    {
        $this->_prepareTriggerDate($rule);
 
        $collection = Mage::getModel('awraf/activity')
                ->getCollection()
                ->addCustomerFilter($rule->getActivity()->getRrId())
                ->addWebsiteFilter($rule->getActivity()->getWebsiteId())
                ->addTypeFilter($rule->getType())
                ->addTriggerFilter($rule->getTriggerActiveFrom());

        $collection->getSelect()
                ->columns(array('amount' => new Zend_Db_Expr("SUM(amount)")));
 
        return $rule->setActivityHistory($collection->getFirstItem());
    }

    public function validateLimit($rule)
    {
        if ($rule->getTrigger() && $rule->getTrigger()->getItemId()) {
            if ($rule->getLimit() && $rule->getLimit() <= $rule->getTrigger()->getLimit()) {
                return false;
            }
            if ($rule->getActionType() == AW_Raf_Model_Rule::PERCENT_TYPE) {
                return false;
            }
        }

        return true;
    }

    protected function _prepareTriggerDate($rule)
    {
        if ($rule->getTrigger()->getItemId()) {
            $rule->setTriggerActiveFrom($rule->getTrigger()->getActiveFrom());
        } else {
            $rule->setTriggerActiveFrom($rule->getActiveFrom());
        }
    }

    public function validate($rule)
    {
        $this->_prepareRuleTrigger($rule);

        if (!$this->validateLimit($rule)) {
            return false;
        }

        $this->_prepareActivityAmount($rule);

        if (($rule->getActivityHistory()->getAmount() + $rule->getTrigger()->getRestAmount()) >= $rule->getTarget()) {
            $rule->getTrigger()->createFromRule($rule);
            return true;
        }

        return false;
    }

}