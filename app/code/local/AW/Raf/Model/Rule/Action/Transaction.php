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


class AW_Raf_Model_Rule_Action_Transaction extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('awraf/rule_action_transaction');
    }

    public function prepare($rule)
    {
        $this->setRuleId($rule->getId())
                ->setCustomerId($rule->getActivity()->getRrId())
                ->setWebsiteId($rule->getActivity()->getWebsiteId())
                ->setTriggerId($rule->getTrigger()->getId())
                ->setDiscount($rule->getAction() * $rule->getTrigger()->getTrigQty())
                ->setCreatedAt(Mage::getModel('core/date')->gmtDate());
        
        if($rule->getComment()) {
            $this->setComment($rule->getComment());
        }
        else {
            $this->setComment(Mage::helper('awraf')->autoMessage($rule->getType()));
        }

        return $this;
    }

    public function updateStats()
    {
        $transport = array(
            'rr_id' => $this->getCustomerId(),
            'website_id' => $this->getWebsiteId(),
            'earned' => $this->getDiscount()
         );

        Mage::getModel('awraf/statistics')->updateStatistics(new Varien_Object($transport));
    }

    public function createFromObject(Varien_Object $obj)
    {
        $this->setData($obj->getData())
                ->setCreatedAt(Mage::getModel('core/date')->gmtDate())
                ->save();
        
        $this->updateStats(); 

        return $this;
    }

    public function getAvailableAmount(Varien_Object $info)
    {
        if (!$info->getWebsiteId() || !$info->getCustomerId()) {
            return false;
        }

        $collection = $this->getCollection()->addFieldFilter($info);

        $collection->getSelect()
                ->columns(array('discount' => new Zend_Db_Expr("SUM(discount)")));


        return $collection->getFirstItem()->getDiscount();
    }

}