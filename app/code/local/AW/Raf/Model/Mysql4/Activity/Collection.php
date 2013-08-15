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


class AW_Raf_Model_Mysql4_Activity_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('awraf/activity');
    }

    public function addCustomerFilter($customer)
    {
        $customer = (array) $customer;

        $this->getSelect()->where('rr_id IN(?)', $customer);

        return $this;
    }

    public function addReferralFilter($referral)
    {
        $referral = (array) $referral;

        $this->getSelect()->where('rl_id IN(?)', $referral);

        return $this;
    }

    public function addTypeFilter($type)
    {
        $type = (array) $type;

        $this->getSelect()->where('type IN(?)', $type);

        return $this;
    }

    public function addWebsiteFilter($website)
    {
        $website = (array) $website;

        $this->getSelect()->where('website_id IN(?)', $website);

        return $this;
    }

    public function addTriggerFilter($date)
    {
        $this->getSelect()->where('created_at > ?', $date);

        return $this;
    }

}