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


class AW_Raf_Model_Mysql4_Referral_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{    
    public $isNativeCount = true;

    public function _construct()
    {
        parent::_construct();
        $this->_init('awraf/referral');
    }

    public function getReferral(Varien_Object $data)
    {
        foreach ($data->toArray() as $key => $val) {
            $val = (array) $val;
            $this->getSelect()->where("main_table.{$key} IN(?)", $val);
        }

        return $this;
    }

    public function getSize()
    {
        if ($this->isNativeCount) {
            return parent::getSize();
        }

        if (is_null($this->_totalRecords)) {
            $sql = $this->getSelectCountSql();
            $this->_totalRecords = count($this->getConnection()->fetchAll($sql, $this->_bindParams));
        }

        return $this->_totalRecords;
    }

    public function setReferrerFilter($referrerId)
    {
        $this->getSelect()->where('main_table.referrer_id IN (?)', (array) $referrerId);

        return $this;
    }

}