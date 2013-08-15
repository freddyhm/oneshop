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


class AW_Raf_Adminhtml_StatsController extends Mage_Adminhtml_Controller_Action
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
                $this->_title($this->__('Statistics'))->_title($root);
            }
        }
        return $this;
    }

    public function indexAction()
    {
        $this
                ->displayTitle('Statistics')
                ->loadLayout()
                ->_setActiveMenu('awraf')
                ->renderLayout();
    }    

    public function exportCsvAction()
    {
        $fileName = 'statistics.csv';
        $content = $this->getLayout()->createBlock('awraf/adminhtml_stats_grid')
                ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'statistics.xml';
        $content = $this->getLayout()->createBlock('awraf/adminhtml_stats_grid')
                ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('awraf/stats');
    }

}