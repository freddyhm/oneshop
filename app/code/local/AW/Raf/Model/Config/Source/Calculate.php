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

    
class AW_Raf_Model_Config_Source_Calculate
{
    const PRICE_AND_TAX = 1;
    const ONLY_PRICE    = 2;

    public function toOptionArray()
    {
        return array(
            array('value'=> self::PRICE_AND_TAX, 'label'=> Mage::helper('awraf')->__('Item price + Tax')),
            array('value'=> self::ONLY_PRICE,    'label'=> Mage::helper('awraf')->__('Item price')),
        );
    }

}