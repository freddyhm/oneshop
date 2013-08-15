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


class AW_Raf_Block_Stats extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $this->_cacheCollection();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getLayout()->getBlock('root')->setHeaderTitle($this->__('Referred Friends'));
    }

    protected function _toHtml()
    {
        $this->getChild('awraf.stats.pager')->setCollection($this->getInvites());

        return parent::_toHtml();
    }

    protected function _cacheCollection()
    {
        $invites = Mage::getResourceModel('awraf/referral_collection')
                ->setReferrerFilter($this->getCustomerId())
                ->addFieldToFilter('main_table.website_id', array('eq' => Mage::app()->getWebsite()->getId()));
               

        $invites->getSelect()
                ->order('main_table.referral_id DESC')
                ->joinLeft(array('activity' => $invites->getTable('awraf/activity')), 'main_table.referral_id = activity.rl_id')
                ->group('main_table.referral_id')
                ->columns(array('items_purchased' => new Zend_Db_Expr("SUM(IF(activity.type = " . AW_Raf_Model_Rule::ORDER_ITEM_QTY_TYPE . ", amount, NULL))")))
                ->columns(array('amount_purchased' => new Zend_Db_Expr("SUM(IF(activity.type = " . AW_Raf_Model_Rule::ORDER_AMOUNT_TYPE . ", amount, NULL))")));

        $invites->isNativeCount = false;        
        
        $this->setInvites($invites);
    }

    public function getActiveBalance()
    {
        $helper = $this->helper('awraf');
        $amount = $helper->getApi()->getAvailableAmount($helper->getCustomerId(), Mage::app()->getWebsite()->getId());
        return $this->formatAmount($amount - $helper->getAppliedAmount());
    }

    public function isConfirmed($invite)
    {
        return $invite->getStatus() == AW_Raf_Model_Activity::STATUS_SIGNUP_CONFIRMED;
    }

    public function getActiveDiscount()
    {
        return Mage::getModel('awraf/api')
                        ->getAvailableDiscount($this->helper('awraf')->getCustomerId(), Mage::app()->getWebsite()->getId())
                        ->getDiscount();
    }

    public function formatAmount($amount)
    {
        return $this->helper('awraf')->convertAmount($amount, array(
                    'format' => true,
                    'store' => Mage::app()->getStore(),
                    'direction' => AW_Raf_Helper_Data::CONVERT_TO_CURRENT
                ));
    }

    public function isInviteAllowed()
    {
        return $this->helper('awraf')->getConfig()->isInviteAllowed($this->getStoreId());
    }

    public function getStoreId()
    {
        return Mage::app()->getStore()->getId();
    }

    public function getCustomerId()
    {
        return Mage::getSingleton('customer/session')->getCustomer()->getId();
    }

    public function getBackUrl()
    {
        return $this->getRefererUrl();
    }

    public function getConverted($value)
    {
        return Mage::app()->getStore()->formatPrice($value);
    }

}
