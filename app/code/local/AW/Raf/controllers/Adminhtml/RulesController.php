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


class AW_Raf_Adminhtml_RulesController extends Mage_Adminhtml_Controller_Action
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
                $this->_title($this->__('Rules'))->_title($root);
            }
        }
        return $this;
    }

    public function indexAction()
    {
        $this
                ->displayTitle('Rules')
                ->loadLayout()
                ->_setActiveMenu('awraf')
                ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $rule = Mage::getModel('awraf/rule')->load($this->getRequest()->getParam('id', false));

        Mage::register('awraf_rule', $rule);
        if ($rule->getId()) {
            $breadcrumbTitle = $breadcrumbLabel = $this->__('Edit Rule');
            $this->displayTitle('Edit Rule');
        } else {
            $breadcrumbTitle = $breadcrumbLabel = $this->__('New Rule');
            $this->displayTitle('New Rule');
        }

        $this
                ->loadLayout()
                ->_setActiveMenu('awraf')
                ->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle)
                ->_addContent($this->getLayout()->createBlock('awraf/adminhtml_rules_edit')
                        ->setData('form_action_url', $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id')))))
                ->_addLeft($this->getLayout()->createBlock('awraf/adminhtml_rules_edit_tabs'))
                ->renderLayout();
    }

    public function resetAllAction()
    {       
        $helper = Mage::helper('awraf');        
        $session = Mage::getSingleton('adminhtml/session');
        
        if((!$sessionKey = $session->getAwrafResetSecret()) || (!$key = $this->getRequest()->getParam('awraf'))) {
            return $this->_redirectReferer();            
        }        
        if ($helper->decodeUrlKey($key) != $sessionKey) {
            return $this->_redirectReferer();
        }

        $resource = Mage::getSingleton('core/resource');
 
        try { 
            $connection = $resource->getConnection('core_write');
            $connection->query("DELETE FROM {$resource->getTableName('awraf/transaction')}");
            $connection->query("DELETE FROM {$resource->getTableName('awraf/discount')}");
            $connection->query("DELETE FROM {$resource->getTableName('awraf/referral')}");
            $connection->query("DELETE FROM {$resource->getTableName('awraf/trigger')}");
            $connection->query("DELETE FROM {$resource->getTableName('awraf/activity')}");
            $connection->query("DELETE FROM {$resource->getTableName('awraf/order_to_ref')}");
            $connection->query("DELETE FROM {$resource->getTableName('awraf/statistics')}");
            $session->addSuccess($helper->__('Information has been successfully resetted'));
        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }

        $session->unsAwrafResetSecret();

        return $this->_redirectReferer();
    }

    public function saveAction()
    {
        $request = new Varien_Object($this->_filterDateTime($this->getRequest()->getParams(), array('active_from')));

        if ($request->getActiveFrom()) {
            $request->setActiveFrom(Mage::getModel('core/date')->gmtDate(null, $request->getActiveFrom()));
        }

        try {
            $rule = Mage::getModel('awraf/rule')->load($request->getId());
            /* status changed to enabled update last trigger to current date */
            if ($rule->getId() && !$rule->getOrigData('status') && $request->getStatus()) {
                Mage::getResourceModel('awraf/trigger')->updateTriggerDate($rule->getId());
            }
            $rule->addData($request->getData())->setActionType($request->getData('type_' . $request->getType()))
                    ->save();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Rule successfully saved'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        if ($request->getBack()) {
            return $this->_redirect('*/*/edit', array('id' => $rule->getId(), 'tab' => $request->getTab()));
        }
        return $this->_redirect('*/*/');
    }

    public function exportCsvAction()
    {
        $fileName = 'rules.csv';
        $content = $this->getLayout()->createBlock('awraf/adminhtml_rules_grid')
                ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'rules.xml';
        $content = $this->getLayout()->createBlock('awraf/adminhtml_rules_grid')
                ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function deleteAction()
    {
        try {
            $request = $this->getRequest()->getParams();

            if (!isset($request['id'])) {
                throw new Mage_Core_Exception($this->__('Incorrect rule'));
            }

            Mage::getModel('awraf/rule')->setId($request['id'])->delete();

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Rule successfully deleted'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        return $this->_redirect('*/*/index');
    }

    public function massDeleteAction()
    {
        try {
            $ruleIds = $this->getRequest()->getParam('rules');

            if (!is_array($ruleIds)) {
                throw new Mage_Core_Exception($this->__('Invalid rule ids'));
            }

            foreach ($ruleIds as $rule) {
                Mage::getSingleton('awraf/rule')->setId($rule)->delete();
            }

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%d rule(s) have been successfully deleted', count($ruleIds)));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('awraf/rules');
    }

}