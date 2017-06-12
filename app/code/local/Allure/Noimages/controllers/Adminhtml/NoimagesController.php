<?php

class Allure_Noimages_Adminhtml_NoimagesController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu($this->_menu_path)
		->_addBreadcrumb(
				Mage::helper('adminhtml')->__('No Image Products'), Mage::helper('adminhtml')->__('No Image Products')
				);
		return $this;
	}

    public function indexAction ()
    {
        $this->_title($this->__("Products With No Images"));
        $this->_initAction();
        $this->renderLayout();
    }

    public function exportCsvAction ()
    {
        $fileName = 'Noimageproducts.csv';
        $grid = $this->getLayout()->createBlock('noimages/adminhtml_noimages_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export order grid to Excel XML format
     */
    public function exportExcelAction ()
    {
        $fileName = 'Noimageproducts.xlxs';
        $grid = $this->getLayout()->createBlock('noimages/adminhtml_noimages_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
