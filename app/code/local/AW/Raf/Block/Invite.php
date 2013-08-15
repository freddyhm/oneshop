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

class AW_Raf_Block_Invite extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aw_raf/invite.phtml');
    }

    public function getFormUrl()
    {
        return $this->getUrl('awraf/index/invitesend', array('_secure' => Mage::app()->getStore(true)->isCurrentlySecure()));
    }

    public function isLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function getFriendName()
    {
        return $this->getData('name');
    }

    public function getSubject()
    {
        if ($this->getData('subject')){
            return $this->getData('subject');
        }
        return $this->__('Referral for %s', Mage::app()->getStore()->getName());
    }

    public function getMessage()
    {
        if ($this->getData('message')){
            return $this->getData('message');
        }
        return $this->__('Hi, I thought it might interest you.');
    }
}
