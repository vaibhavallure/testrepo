<?php


class Allure_Inventory_Adminhtml_Inventory_ReportsController extends Allure_Inventory_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu($this->_menu_path)
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Manage Stock'), Mage::helper('adminhtml')->__('Manage Stock')
        );
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
    	$this->_initAction();
    	$this->_title($this->__('Inventory'))
    	->_title($this->__('Manage Stock'));
    		$this->renderLayout();
    }


    /**
     * save item action
     */
   
    public function stockreceiveAction() {
   
    	$admin = Mage::getSingleton('admin/session')->getUser();
    	$this->_title($this->__('Reports'))
    	->_title($this->__('Stock Receiving'));
    	 
    	$this->_initAction()
    	->_setActiveMenu('report/product/lowstock')
    	->_addBreadcrumb(Mage::helper('reports')->__('Low Stock'), Mage::helper('reports')->__('Low Stock'))->renderLayout();
    	
    }
    public function minmaxAction() {
       
        $admin = Mage::getSingleton('admin/session')->getUser();
        $this->_title($this->__('Reports'))
        ->_title($this->__('Min max'));
        
        $this->_initAction()
        ->_setActiveMenu('report/product/minmax')
        ->_addBreadcrumb(Mage::helper('reports')->__('Min max'), Mage::helper('reports')->__('Min max'))->renderLayout();
        
    }
    public function stocktransferAction()
    {
    	
    	$this->loadLayout();
    	$this->_title($this->__('Reports'))
    	->_title($this->__('Stock Transfer'));
    	$this->renderLayout();
    }
    public function lowstockAction()
    {
    
    	$this->loadLayout();
    	$this->_title($this->__('Reports'))
    	->_title($this->__('Low Stock'));
    	$this->renderLayout();
    }
    
    public function exportDownloadsExcelAction()
    {
    	$fileName   = 'inventory_receive.xlsx';
    	$content    = $this->getLayout()->createBlock('inventory/adminhtml_reports_stockreceive_grid')
    	->setSaveParametersInSession(true)
    	->getExcel($fileName);
    
    	$this->_prepareDownloadResponse($fileName, $content);
    }
    public function exportDownloadsCsvAction()
    {
    	$fileName   = 'inventory_receive.csv';
    	$content    = $this->getLayout()->createBlock('inventory/adminhtml_reports_stockreceive_grid')
    	->setSaveParametersInSession(true)
    	->getCsv();
    	$this->_prepareDownloadResponse($fileName, $content);
    }
    public function exportTransferExcelAction()
    {
        $fileName   = 'inventory_transfer.xlsx';
        $content    = $this->getLayout()->createBlock('inventory/adminhtml_reports_transfer_grid')
        ->setSaveParametersInSession(true)
        ->getExcel($fileName);
        
        $this->_prepareDownloadResponse($fileName, $content);
    }
    public function exportTransferCsvAction()
    {
        $fileName   = 'inventory_transfer.csv';
        $content    = $this->getLayout()->createBlock('inventory/adminhtml_reports_transfer_grid')
        ->setSaveParametersInSession(true)
        ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    public function exportSalesminmaxExcelAction()
    {
        $fileName   = 'inventory_minmax.xlsx';
        $content    = $this->getLayout()->createBlock('inventory/adminhtml_reports_minmax_grid')
        ->setSaveParametersInSession(true)
        ->getExcel($fileName);
        
        $this->_prepareDownloadResponse($fileName, $content);
    }
    public function exportSalesminmaxCsvAction()
    {
        $fileName   = 'inventory_minmax.csv';
        $content    = $this->getLayout()->createBlock('inventory/adminhtml_reports_minmax_grid')
        ->setSaveParametersInSession(true)
        ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    public function exportLowStcokExcelAction()
    {
    	$websiteId=1;
    	if(Mage::getSingleton('core/session')->getMyWebsiteId())
    		$websiteId=Mage::getSingleton('core/session')->getMyWebsiteId();
    	$website=Mage::getModel( "core/website" )->load($websiteId);
    	$date = Mage::getModel('core/date')->date('Y_m_d_H-i-s');
    	$fileName   = 'lowstock_'.$website->getName().$date.'.xlsx';
    	$content    = $this->getLayout()->createBlock('inventory/adminhtml_lowstock_grid')
    	->setSaveParametersInSession(true)
    	->getExcel($fileName);
    	$this->_prepareDownloadResponse($fileName, $content);
    }
    
}
