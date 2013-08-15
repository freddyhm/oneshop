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


class AW_Raf_Helper_Data extends Mage_Core_Helper_Abstract
{

    const CONVERT_TO_BASE = 1;
    const CONVERT_TO_CURRENT = 2;

    public function getReferafriendUrl()
    {
        return Mage::getUrl('awraf/index/invite', array('_secure' => Mage::app()->getStore(true)->isCurrentlySecure()));
    }

    public function getConfig()
    {
        return Mage::helper('awraf/config');
    }

    public function encodeUrlKey($key)
    {
        return $this->getEncryptor()->urlEncode($this->getEncryptor()->encrypt($key));
    }

    public function decodeUrlKey($key)
    {
        return $this->getEncryptor()->decrypt($this->getEncryptor()->urlDecode($key));
    }

    public function getEncryptor()
    {
        return Mage::helper('core');
    }

    public function autoMessage($type)
    {
        return Mage::getSingleton('awraf/config_source_ruleType')->getAutoMessage($type);
    }

    public function getCustomerId()
    {
        return $this->getCustomer()->getId();
    }

    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /* collect totals methods */

    // applied amount
    public function setAppliedAmount($amount)
    {
        $this->_session()->setRafMoneyCustomer($amount);
    }

    public function clearAppliedAmount()
    {
        $this->_session()->setRafMoneyCustomer(0);
    }

    public function getAppliedAmount()
    {
        return $this->_session()->getRafMoneyCustomer();
    }

    public function setReservedAmount($amount)
    {
        $this->_session()->setRafReservedAmount($amount);
    }

    public function getReservedAmount()
    {
        return $this->_session()->getRafReservedAmount();
    }

    /* manage discountes methods */

    public function setDiscountByType($type, $amount, $index = 0)
    {
        $session = $this->_session();
        $discountByAddress = (array) $session->getRafDiscountByAddress();
        $discountByAddress["{$index}_{$type}"] = $amount;
        $session->setRafDiscountByAddress($discountByAddress);
    }

    public function getDiscountByType($type, $index = 0)
    {
        $session = $this->_session();
        $discountByAddress = (array) $session->getRafDiscountByAddress();
        if (isset($discountByAddress["{$index}_{$type}"])) {
            return $discountByAddress["{$index}_{$type}"];
        }
        return false;
    }

    public function clearDiscountByType($type, $index = 0)
    {
        $session = $this->_session();
        $discountByAddress = (array) $session->getRafDiscountByAddress();
        if (isset($discountByAddress["{$index}_{$type}"])) {
            unset($discountByAddress["{$index}_{$type}"]);
        }
        $session->setRafDiscountByAddress($discountByAddress);
    }

    /* manage discounts by type */
    
    public function clearSession()
    {
        $this->_session()->unsRafDiscountByAddress();
        $this->setAppliedDiscount(0);
        $this->setAppliedAmount(0);
        $this->setReservedAmount(0);
    }

    public function setAppliedDiscount($amount)
    {
        $this->_session()->setRafDiscountCustomer($amount);
    }

    public function clearAppliedDiscount()
    {
        $this->_session()->setRafDiscountCustomer(0);
    }

    public function getAppliedDiscount()
    {
        return $this->_session()->getRafDiscountCustomer();
    }

    protected function _session()
    {
        return Mage::getSingleton('customer/session');
    }

    /* */

    public function getApi()
    {
        return Mage::getSingleton('awraf/api');
    }

    public function magentoLess14()
    {
        return version_compare(Mage::getVersion(), '1.4', '<');
    }

    public function getNotification()
    {
        return Mage::helper('awraf/notifications');
    }

    /**
     * Convert price to different directions
     * @param float amount
     * @param array $data
     * @return float
     * @throws Exception
     */
    public function convertAmount($amount, array $data)
    {
        $amount = $this->convertToFloat($amount, isset($data['locale']) ? $data['locale'] : Mage::app()->getLocale());
        
        if (!isset($data['store'])) {
            throw new Exception('Convert amount error: sore is not set');
        }
        $store = $data['store'];
        if (is_int($store)) {
            $store = Mage::getModel('core/store')->load($data['store']);
        }
        if (!$store instanceof Mage_Core_Model_Store || is_null($store->getId())) {
            throw new Exception("Convert amount error: store does not exist");
        }
        if (!isset($data['direction'])) {
            throw new Exception('Convert amount error: convert direction is not set');
        }
        if ($store->getCurrentCurrency() && $store->getBaseCurrency()) {
            if ($data['direction'] == self::CONVERT_TO_CURRENT) {                
                $amount = $store->getBaseCurrency()->convert($amount, $store->getCurrentCurrency());   
                if(isset($data['floor'])) {
                    $amount = $this->getMath()->floorFloat($amount);
                }
            } else {
                $dirrectory = Mage::getModel('directory/currency')->load($store->getBaseCurrency());
                $amount = $amount / $dirrectory->getRate($store->getCurrentCurrency());
                if(isset($data['floor'])) {
                    return $this->getMath()->floorFloat($amount);
                }
            }
        }
          
        if (isset($data['format'])) {
            $amount = $store->formatPrice($amount, false);
        }

        return $amount;
    }
    
    public function getCurrentRate(Mage_Core_Model_Store $store)
    {
        $dirrectory = Mage::getModel('directory/currency')->load($store->getBaseCurrency());
        return $dirrectory->getRate($store->getCurrentCurrency());
    }

    public function convertToFloat($value, $locale)
    {
        $value = preg_replace("#[^.,0-9]#isu", "", $value);
         
        return  (float) $value;
        
        
        $locale = new Zend_Locale($locale->getLocaleCode());

        try {
            return Zend_Locale_Format::getNumber($value, array('locale' => $locale, 'precision' => 2));
        } catch (Exception $e) {
            return (float) $value;
        }
    }

    public function getQuote()
    {
        return Mage::helper('awraf/quote');
    }
    
    public function getMath()
    {
        return Mage::helper('awraf/bcmath')->scale();
    }
    
    public function getOrder()
    {
        return Mage::helper('awraf/order');
    }

    /**
     * Write referrer id
     * SESSION
     * COOKIE
     * @param int $val
     * @return mixed
     */
    public function setReferrer($val)
    {
        if (!$this->getReferrer()) {
            Mage::getSingleton('customer/session')->setAwrafReferrer($val);
            return Mage::getModel('core/cookie')->set(AW_Raf_Model_Activity::COOKIE_NAME, $val, true);
        }

        return false;
    }

    /**
     * Get referrer from 
     * COOKIE
     * SESSION
     * @return boolean
     */
    public function getReferrer()
    {
        $cookieRef = (int) Mage::getModel('core/cookie')->get(AW_Raf_Model_Activity::COOKIE_NAME);

        if ($cookieRef) {
            return $cookieRef;
        }

        $sessionRef = Mage::getSingleton('customer/session')->getAwrafReferrer();

        if ($sessionRef) {
            return $sessionRef;
        }

        return false;
    }

    /**
     * Clear referrer cookie and session info
     */
    public function unsReferrer()
    {
        Mage::getModel('core/cookie')->delete(AW_Raf_Model_Activity::COOKIE_NAME);
        Mage::getSingleton('customer/session')->setAwrafReferrer(null);
    }

    /**
     * Write referrer id
     * SESSION
     * COOKIE
     * @param int $val
     * @return mixed
     */
    public function setReferral($val)
    {
        if (!$this->getReferral()) {
            Mage::getSingleton('customer/session')->setAwrafReferral($val);
            return Mage::getModel('core/cookie')->set(AW_Raf_Model_Activity::COOKIE_REFERRAL, $val, true);
        }

        return false;
    }

    public function unsReferral()
    {
        Mage::getModel('core/cookie')->delete(AW_Raf_Model_Activity::COOKIE_REFERRAL);
        Mage::getSingleton('customer/session')->setAwrafReferral(null);
    }

    /**
     * Get referral from 
     * COOKIE
     * SESSION
     * @return boolean
     */
    public function getReferral()
    {
        $cookieRef = (int) Mage::getModel('core/cookie')->get(AW_Raf_Model_Activity::COOKIE_REFERRAL);

        if ($cookieRef) {
            return $cookieRef;
        }

        $sessionRef = Mage::getSingleton('customer/session')->getAwrafReferral();

        if ($sessionRef) {
            return $sessionRef;
        }

        return false;
    }

    /**
     * @param int $ref
     * @return str
     */
    public function getReferrerLink($ref = null, $rel = null)
    {
        $urlKey = null;
        if ($ref) {
            $urlKey = $this->encodeUrlKey($ref);
        } else {
            $session = Mage::getSingleton('customer/session');
            if ($session->isLoggedIn()) {
                $urlKey = $this->encodeUrlKey($session->getCustomer()->getId());
            }
        }

        if ($urlKey) {
            $queryData = array('ref' => $urlKey);
            if ($rel) {
                $queryData['rel'] = $this->encodeUrlKey($rel);
            }
            return Mage::getUrl('', array(
                        '_query' => $queryData,
                        '_secure' => Mage::app()->getStore(true)->isCurrentlySecure(),
                        '_store' => Mage::app()->getStore()->getId(),
                        '_store_to_url' => true)
            );
        }

        return Mage::getBaseUrl();
    }

}
