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


/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AW_Raf_Block_Checkout_Cart_Discount extends Mage_Core_Block_Template
{
    protected $_template = 'aw_raf/checkout/cart/discount.phtml';

    public function getAvailableAmount($baseAmount = false, $format = true)
    {
        $helper = $this->helper('awraf');

        $availableAmount = $helper->getApi()->getAvailableAmount($helper->getCustomerId(), Mage::app()->getWebsite()->getId());

        if ($baseAmount) {
            return $availableAmount;
        }

        $amount = $helper->getMath()->sub($availableAmount, $helper->getAppliedAmount());
        
        $store = Mage::app()->getStore();
        return $helper->convertAmount(max(0, $amount), array(
                    'store' => $store, 
                    'floor' => true,
                    'format' => $format, 
                    'direction' => AW_Raf_Helper_Data::CONVERT_TO_CURRENT)
        );

        return $amount;
    }
    
    public function getNumericAmount()
    {
        return $this->getAvailableAmount(false, null);
    }
 
    public function cancelAllowed()
    {
        return $this->helper('awraf')->getAppliedAmount();
    }

    public function getMaxDiscount()
    {
        return $this->helper('awraf')->getConfig()->maxDiscount(Mage::app()->getStore()->getId());
    }

    public function discountAllowed()
    {
        $helper = $this->helper('awraf');

        $store = Mage::app()->getStore();

        $address = $helper->getQuote()->getQuoteAddress();

        if (!$address) {            
            return false;
        }

        if (!$helper->getQuote()->setStore($store->getId())
                        ->setAddress($address)
                        ->shouldRender()) {
            return false;
        }

        $minDiscount = $helper->getConfig()->minDiscount($store->getId());

        $availableAmount = $this->getAvailableAmount(true);

        if ((!$minDiscount || $minDiscount < 0) && $availableAmount > 0) {
            return true;
        }

        if ($minDiscount < $availableAmount) {
            return true;
        }

        return false;
    }

}
