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


class AW_Raf_Model_Rule extends AW_Raf_Model_Rule_Abstract
{
    /* activity types */
    const SIGNUP_TYPE = 1;
    const ORDER_AMOUNT_TYPE = 2;
    const ORDER_ITEM_QTY_TYPE = 3;
    const BONUS_REGISTER = 0;
    const ORDER_FEE = 4;
    /* action types */
    const FIXED_TYPE = 1;
    const PERCENT_TYPE = 2;
    /* validator types */
    const SIGNUP_TYPE_INSTANCE = 'signup';
    const ORDER_AMOUNT_TYPE_INSTANCE = 'amount';
    const ORDER_ITEM_QTY_TYPE_INSTANCE = 'qty';
    /* action types */
    const TRANSACTION_TRIGGER = 'transaction';
    const DISCOUNT_TRIGGER = 'discount';
    const RULES_TRIGGER = 'rule';

    protected $_validatorTypes = array();
    protected $_actionTypes = array();

    public function _construct()
    {
        parent::_construct();
        $this->_init('awraf/rule');
    }

    /**
     * @return boolean
     */
    public function validate()
    {
        foreach ($this->getValidators() as $validator) {
            if (!$validator->validate($this)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return AW_Raf_Model_Rule obj
     */
    public function trigger()
    {
        $transaction = Mage::getModel('core/resource_transaction');
        
        $triggers = $this->getTriggers();

        foreach ($triggers as $trigger) {

            $result = $trigger->prepare($this);

            if (!is_array($result)) {
                $result = array($result);
            }

            foreach ($result as $object) {
                if (!$object instanceof Mage_Core_Model_Abstract) {
                    continue;
                }

                $transaction->addObject($object);
            }
        }

        $transaction->save();

        foreach ($triggers as $trigger) {
            if(method_exists($trigger, 'updateStats')) {
                $trigger->updateStats();
            }
        }

        return $this;
    }

    /**
     * Usage: 
     *  key -> string
     *  value -> string|array of validator types
     * @return array
     */
    public function getValidatorTypes()
    {
        if (!empty($this->_validatorTypes)) {
            return $this->_validatorTypes;
        }

        return array(
            self::SIGNUP_TYPE => self::SIGNUP_TYPE_INSTANCE,
            self::ORDER_AMOUNT_TYPE => self::ORDER_AMOUNT_TYPE_INSTANCE,
            self::ORDER_ITEM_QTY_TYPE => self::ORDER_ITEM_QTY_TYPE_INSTANCE
        );
    }

    public function setValidatorTypes(array $types)
    {
        $this->_validatorTypes = $types;
    }

    /**
     * Usage:
     *  key -> string
     *  value -> string|array of action types
     * @return array
     */
    public function getActionTypes()
    {
        if (!empty($this->_actionTypes)) {
            return $this->_actionTypes;
        }

        return array(
            self::FIXED_TYPE => self::TRANSACTION_TRIGGER,
            self::PERCENT_TYPE => self::DISCOUNT_TRIGGER
        );
    }

    public function setActionTypes(array $types)
    {
       $this->_actionTypes = $types;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getTriggers()
    {
        $trigger = $this->getActionType();

        if (!$trigger) {
            throw new Exception("Rule action type is not set");
        }

        $actions = $this->getActionTypes();

        if (!isset($actions[$trigger])) {
            throw new Exception("Action of type {$trigger} has not been registered. Check getActionTypes method");
        }

        $triggers = (array) $actions[$trigger];

        $objects = array();
        foreach ($triggers as $trigger) {

            $class = "awraf/rule_action_{$trigger}";

            $model = Mage::getModel($class);

            if ($model instanceof Mage_Core_Model_Abstract) {
                $objects[] = $model;
                continue;
            }

            throw new Exception("Action trigger  $class not found");
        }

        return $objects;
    }

    /**
     * 
     * @return array
     * @throws Exception
     */
    public function getValidators()
    {
        $type = $this->getType();

        if (!$type) {
            throw new Exception("Rule type is not set");
        }

        $types = $this->getValidatorTypes();

        if (!isset($types[$type])) {
            throw new Exception("Validator of type {$type} has not been registered. Check getValidatorTypes method");
        }

        $validators = (array) $types[$type];

        $objects = array();
        foreach ($validators as $validator) {

            $class = "awraf/rule_validator_{$validator}";

            $model = Mage::getModel($class);

            if ($model instanceof Mage_Core_Model_Abstract) {
                $objects[] = $model;
                continue;
            }

            throw new Exception("Validator {$class} not found");
        }

        return $objects;
    }

}