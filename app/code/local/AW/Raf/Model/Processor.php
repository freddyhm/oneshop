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


class AW_Raf_Model_Processor extends Mage_Core_Model_Abstract
{
    protected $_activity;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('awraf/processor');
    }

    /**
     * @param array $types
     * @throws Exception
     */
    public function process(Varien_Object $transport)
    {
        $types = $transport->getTypes();

        if (!is_array($types) || empty($types)) {
            throw new Exception('No types for processing');
        }

        $activity = Mage::getModel('awraf/activity')->register($transport);
        
        $rules = Mage::getModel('awraf/rule')->getCollection()
                ->addTypeFilter($types)
                ->addEnabledFilter()
                ->addActiveFromFilter()
                ->addStoreFilter($transport->getStoreId())
                ->addPriorityOrder();  
      
        $stop = array();
        foreach ($rules as $rule) {         
            if(isset($stop[$rule->getType()])) {               
                $rule->setSkipTransaction(true);
            }           
            if (!$activityTypeObj = $activity->getActivityByType($rule->getType())) {
                continue;
            }
            if ($rule->setActivity($activityTypeObj)->validate()) {
                if($rule->getStopOnFirst()) {
                    $stop[$rule->getType()] = true;
                }
                if($rule->getSkipTransaction()) {
                    continue;
                }
                $rule->trigger();
            }
        }
    }
}