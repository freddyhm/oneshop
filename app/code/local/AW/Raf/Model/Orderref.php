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


class AW_Raf_Model_Orderref extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('awraf/orderref');
    }

    public function getByOrder(Mage_Sales_Model_Order $order)
    {
        $this->load($order->getIncrementId(), 'order_increment');
    }

    public function getTotalDiscount(Mage_Sales_Model_Order $order)
    {
        $this->getByOrder($order);

        if (!$this->getId()) {
            return false;
        }

        $orderInfo = new Varien_Object(Zend_Json::decode($this->getOrderInfo()));

        return $orderInfo->getAppliedDiscount() + $orderInfo->getAppliedAmount();
    }

}