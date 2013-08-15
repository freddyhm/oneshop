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


class AW_Raf_Model_Trigger extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('awraf/trigger');
    }

    public function createFromRule(AW_Raf_Model_Rule $rule)
    {        
        $amount = ($rule->getActivityHistory()->getAmount() + $rule->getTrigger()->getRestAmount());       
        $instance = $this->getInstance()
                ->setCustomerId($rule->getActivity()->getRrId())
                ->setRuleId($rule->getId())
                ->setTrigQty(floor($amount / $rule->getTarget()))
                ->setRestAmount(fmod($amount, $rule->getTarget()))
                ->setCreatedAt(Mage::getModel('core/date')->gmtDate())
                ->save();
        /* overwrite trigger object */
        $rule->setTrigger($instance);

        return $this;
    }

    public function getInstance()
    {
        return new self();
    }

}