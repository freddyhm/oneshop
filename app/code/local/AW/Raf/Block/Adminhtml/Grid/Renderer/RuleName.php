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


class AW_Raf_Block_Adminhtml_Grid_Renderer_RuleName extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function _getValue(Varien_Object $row)
    {
        if (is_null($row->getRuleId())) {
            return parent::_getValue($row);
        }

        $title = trim($row->getRuleName()) ? trim($row->getRuleName()) : "#{$row->getRuleId()}";

        $url = Mage::helper('adminhtml')->getUrl('awraf_admin/adminhtml_rules/edit', array('id' => $row->getRuleId()));

        return "<a href='{$url}'>{$title}</a>";
    }
}

