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




class AW_Raf_Model_Config_Source_ActionType
{

    protected $_helper;

    public function __construct()
    {
        $this->_helper = Mage::helper('awraf');
    }

    public function signupLabels()
    {
        return array(
            $this->_helper->__('Fixed flat rate discount for signup quantity'),
            $this->_helper->__('Fixed % discount for signup quantity')
        );
    }

    public function amountLabels()
    {
        return array(
            $this->_helper->__('Fixed flat rate discount for all referrals purchase amount'),
            $this->_helper->__('Fixed % discount for all referrals purchase amount')
        );
    }

    public function qtyLabels()
    {
        return array(
            $this->_helper->__('Fixed flat rate discount for quantity of the items purchased'),
            $this->_helper->__('Fixed % discount for quantity of the items purchased')
        );
    }

    public function toOptionArray($type = null)
    {
        $options = array(
            AW_Raf_Model_Rule::FIXED_TYPE => $this->_helper->__('Fixed Discount'),
            AW_Raf_Model_Rule::PERCENT_TYPE => $this->_helper->__('Percent Discount')
        );

        if ($type) {
            $method = "{$type}Labels";
            if (method_exists($this, $method)) {
                $labels = $this->{$method}();
                foreach ($options as &$label) {
                    $label = array_shift($labels);
                }
            }
        }

        return $options;
    }

    public function toLabel($type, $label)
    {
        $options = $this->toOptionArray($type);

        if (isset($options[$label])) {
            return $options[$label];
        }

        return false;
    }

}