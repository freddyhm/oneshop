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


class AW_Raf_Adminhtml_TransactionsController extends Mage_Adminhtml_Controller_Action
{
    protected function displayTitle($data = null, $root = 'Refer a Friend')
    {
        if (!Mage::helper('awraf')->magentoLess14()) {
            if ($data) {
                if (!is_array($data)) {
                    $data = array($data);
                }
                $this->_title($this->__($root));
                foreach ($data as $title) {
                    $this->_title($this->__($title));
                }              
            } else {
                $this->_title($this->__('Transactions'))->_title($root);
            }
        }
        return $this;
    }

    public function indexAction()
    {
        $this
                ->displayTitle('Transactions')
                ->loadLayout()
                ->_setActiveMenu('awraf')
                ->renderLayout();
    }

    public function gridAction()
    {
        return $this->getResponse()->setBody($this->getLayout()->createBlock('awraf/adminhtml_customer_grid')->toHtml());
    }

    public function newAction()
    {
        $breadcrumbTitle = $breadcrumbLabel = $this->__('Create Transaction');
        $this->displayTitle('Create Transaction');

        $this
                ->loadLayout()
                ->_setActiveMenu('awraf')
                ->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle)
                ->renderLayout();
    }
 
    public function saveAction()
    {
        $request = new Varien_Object($this->getRequest()->getParams());
 
        if (!is_null($request->getSelectedValues())) {
            return $this->massDiscountAdd($request);
        }
        try {
            $rule = Mage::getModel('awraf/rule_action_transaction')->load($request->getId())
                    ->setWebsiteId($request->getRafWebsite())
                    ->addData($request->getData())
                    ->save();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Transaction successfully saved'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        if ($request->getBack()) {
            return $this->_redirect('*/*/edit', array('id' => $rule->getId(), 'tab' => $request->getTab()));
        }
        return $this->_redirect('*/*/');
    }

    public function massDiscountAdd($request)
    {        
        if(!$request->getSelectedValues()) {
             Mage::getSingleton('adminhtml/session')->addNotice($this->__('Please select at least one customer')); 
             Mage::getSingleton('adminhtml/session')->setFormData($request->getData());  
             return $this->_redirect('*/*/new');
        } 
        
        $transport = new Varien_Object(array(
                    'website_id' => $request->getRafWebsite(),
                    'discount' => $request->getDiscount(),
                    'comment' => $request->getComment(),
                    'trigger' => AW_Raf_Model_Rule::TRANSACTION_TRIGGER,
                    'type' => AW_Raf_Model_Rule::FIXED_TYPE
                ));

        $err = null;
        foreach (explode(',', $request->getSelectedValues()) as $val) {
            try {
                Mage::getModel('awraf/api')->add(
                        $transport
                                ->setData('customer_id', $val)
                                ->setData('created_at', gmdate('Y-m-d H:i:s'))
                );
            } catch (Exception $e) {
                Mage::logException($e);
                $err = true;
            }
        }

        if ($err) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Some transactions were not added correctly. For more details see exceptions log'));
        } else {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Transactions Added'));
        }
        return $this->_redirect('*/*/');
    }

    public function exportCsvAction()
    {
        $fileName = 'transactions.csv';
        $content = $this->getLayout()->createBlock('awraf/adminhtml_transaction_grid')
                ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'discounts.xml';
        $content = $this->getLayout()->createBlock('awraf/adminhtml_transaction_grid')
                ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('awraf/transactions');
    }

}