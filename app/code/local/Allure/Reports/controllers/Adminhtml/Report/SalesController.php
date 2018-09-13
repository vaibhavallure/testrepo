<?php
require_once 'Mage/Adminhtml/controllers/Report/SalesController.php';
class Allure_Reports_Adminhtml_Report_SalesController extends Mage_Adminhtml_Report_SalesController
{

    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());
        return true;
        switch ($action) {
            case 'sales':
                return $this->_getSession()->isAllowed('report/salesroot/sales');
                break;
            case 'salesreport':
                return true;//$this->_getSession()->isAllowed('report/salesroot/sales');
                break;
            case 'tax':
                return $this->_getSession()->isAllowed('report/salesroot/tax');
                break;
            case 'shipping':
                return $this->_getSession()->isAllowed('report/salesroot/shipping');
                break;
            case 'invoiced':
                return $this->_getSession()->isAllowed('report/salesroot/invoiced');
                break;
            case 'refunded':
                return $this->_getSession()->isAllowed('report/salesroot/refunded');
                break;
            case 'coupons':
                return $this->_getSession()->isAllowed('report/salesroot/coupons');
                break;
            case 'shipping':
                return $this->_getSession()->isAllowed('report/salesroot/shipping');
                break;
            case 'bestsellers':
                return $this->_getSession()->isAllowed('report/products/bestsellers');
                break;
            default:
                return $this->_getSession()->isAllowed('report/salesroot');
                break;
        }
    }
    
    
    public function salesreportAction()
    {
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('Sales'));

       // $this->_showLastExecutionTime(Mage_Reports_Model_Flag::REPORT_ORDER_FLAG_CODE, 'sales');

        $this->_initAction()
            ->_setActiveMenu('report/sales/salesreport')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Sales Report'), Mage::helper('adminhtml')->__('Sales Report'));

        $gridBlock = $this->getLayout()->getBlock('allure_reports/adminhtml_sales_sales_grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }
    public function catalogreportAction()
    {
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('Sales'));
        
        // $this->_showLastExecutionTime(Mage_Reports_Model_Flag::REPORT_ORDER_FLAG_CODE, 'sales');
        
        $this->_initAction()
        ->_setActiveMenu('report/sales/catalogreport')
        ->_addBreadcrumb(Mage::helper('adminhtml')->__('Sales Report'), Mage::helper('adminhtml')->__('Sales Report'));
        
        $gridBlock = $this->getLayout()->getBlock('allure_reports/adminhtml_catalog_sales_grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');
        
        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));
        
        $this->renderLayout();
    }


    /**
     * Export sales report grid to CSV format
     */
    public function exportSalesreportCsvAction()
    {
        $fileName   = 'sales.csv';
        $grid       = $this->getLayout()->createBlock('allure_reports/adminhtml_sales_sales_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    public function exportCatalogreportCsvAction()
    {
        $fileName   = 'sales.csv';
        $grid       = $this->getLayout()->createBlock('allure_reports/adminhtml_catalog_sales_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export sales report grid to Excel XML format
     */
    public function exportSalesreportExcelAction()
    {
        $fileName   = 'sales.xml';
        $grid       = $this->getLayout()->createBlock('allure_reports/adminhtml_sales_sales_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
    public function exportCatalogreportExcelAction()
    {
        $fileName   = 'sales.xml';
        $grid       = $this->getLayout()->createBlock('allure_reports/adminhtml_catalog_sales_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

}
