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


class AW_Raf_Helper_Bcmath extends Mage_Core_Helper_Abstract
{    
    public function scale($scale = 2)
    {
        bcscale($scale);
        
        return $this;
    }
    
    public function sub($x, $y)
    {
        return (float) bcsub($x, $y);
    }

    public function mult($x, $y)
    {
        return (float) bcmul($x, $y);
    }
    
    public function bcadd($x, $y)
    {
        return (float) bcadd($x, $y);
    }
    
    public function comp($x, $y)
    {
       return (float) bccomp($x, $y);        
    }
    
    public function div($x, $y)
    {
       return (float) bcdiv($x, $y);
    }
    
    public function floorFloat($x)
    {
        return (float) floor(bcmul($x, 100)) / 100;
    } 
}