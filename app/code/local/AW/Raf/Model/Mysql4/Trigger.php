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


class AW_Raf_Model_Mysql4_Trigger extends Mage_Core_Model_Mysql4_Abstract
{

    protected function _construct()
    {
        $this->_init('awraf/trigger', 'transaction_id');
    }

    public function updateTriggerDate($rule)
    {
        $pdo = $this->_getReadAdapter()
                ->query("SELECT `trigger`.`item_id` FROM 
                        (SELECT * FROM {$this->getMainTable()} WHERE `rule_id` = {$rule} ORDER BY `created_at` DESC) as `trigger` 
                    GROUP BY customer_id, rule_id");

        $triggers = $pdo->fetchAll(PDO::FETCH_COLUMN, 0);

        if (empty($triggers)) {
            return $this;
        }

        $this->_getWriteAdapter()
                ->update($this->getMainTable(), 
                        array('created_at' => Mage::getModel('core/date')->gmtDate()), 
                        array('item_id IN(?)' => $triggers));
    }

}