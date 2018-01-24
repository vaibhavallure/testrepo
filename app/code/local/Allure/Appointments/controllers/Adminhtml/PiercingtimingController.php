<?php
class Allure_Appointments_Adminhtml_PiercingtimingController extends Mage_Adminhtml_Controller_Action
{

	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Appointments"));
	   $this->_addContent($this->getLayout()->createBlock('appointments/adminhtml_piercingtiming'));
	   $this->renderLayout();
    }
    protected function _isAllowed()
    {
    	return true;
    }
    
    public function gridAction()
    {
    	$this->loadLayout();
    	$this->getResponse()->setBody(
    			$this->getLayout()->createBlock('appointments/adminhtml_piercingtiming_grid')->toHtml()
    			);
    }
    
    public function editAction ()
    {
    	$this->_title($this->__("Edit Service Locations"));
    
    	$id = $this->getRequest()->getParam("id");
    	$model = Mage::getModel('appointments/timing')->load($id);
    	if ($model->getId()) {
    		Mage::register('appointment_piercing_timing_data', $model);
    		$this->loadLayout();
    		$this->_setActiveMenu("allure/appointments");
    		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Service Location Manager"),
    				Mage::helper("adminhtml")->__("Service Location Manager"));
    		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Service Location Description"),
    				Mage::helper("adminhtml")->__("Service Location Description"));
    		$this->getLayout()
    		->getBlock("head")
    		->setCanLoadExtJs(true);
    		$this
    		->_addContent($this->getLayout()->createBlock('appointments/adminhtml_piercingtiming_edit'))
    		->_addLeft($this->getLayout()->createBlock('appointments/adminhtml_piercingtiming_edit_tabs'))
    		;
    				$this->renderLayout();
    	} else {
    		Mage::getSingleton("adminhtml/session")->addError(
    				Mage::helper("appointments")->__("Service Location does not exist."));
    		$this->_redirect("*/*/");
    	}
    }
    
    public function newAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$model = Mage::getModel('appointments/timing')->load($id);
    
    	$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
    	if (!empty($data)) {
    		$model->setData($data);
    	}
    
    	$this->loadLayout();
    	//$this->_setActiveMenu('blog/posts');
    	$this->_title('Add new Service Location');
    
    	$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
    
    	$this
    	->_addContent($this->getLayout()->createBlock('appointments/adminhtml_piercingtiming_edit'))
    	->_addLeft($this->getLayout()->createBlock('appointments/adminhtml_piercingtiming_edit_tabs'))
    	;
    	$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
    	$this->renderLayout();
    }
    
    public function saveAction ()
    {
    	$post_data = $this->getRequest()->getPost();
    	if ($post_data) {
    
    		if(!$post_data['qty']){
    			Mage::getSingleton("adminhtml/session")->addError(Mage::helper("appointments")->__("No of Piercing is required"));
    			$this->_redirect("*/*/");
    		}
    		try {
    
    			$model = Mage::getModel('appointments/timing')->addData($post_data)
    			->setId($this->getRequest()
    					->getParam("id"))
    					->save();
    			
    		    //add logs
    			$helperLogs = $this->getLogsHelper();
    			$helperLogs->saveLogs("admin");
    
    					Mage::getSingleton("adminhtml/session")->addSuccess(
    							Mage::helper("adminhtml")->__("Piercing Timing saved sucessfully"));
    					Mage::getSingleton("adminhtml/session")->setPiercingtimingData(false);
    
    					if ($this->getRequest()->getParam("back")) {
    						$this->_redirect("*/*/edit", array(
    								"id" => $model->getId()
    						));
    						return;
    					}
    					$this->_redirect("*/*/");
    					return;
    		} catch (Exception $e) {
    			Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
    			Mage::getSingleton("adminhtml/session")->setPiercingtimingData($this->getRequest()
    					->getPost());
    			$this->_redirect("*/*/edit", array(
    					"id" => $this->getRequest()
    					->getParam("id")
    			));
    			return;
    		}
    	}
    	$this->_redirect("*/*/");
    }
    
    public function deleteAction ()
    {
    	if ($this->getRequest()->getParam("id") > 0) {
    		try {
    			$model = Mage::getModel('appointments/timing');
    			$model->setId($this->getRequest()
    					->getParam("id"))
    					->delete();
    			
    			//add logs
    			$helperLogs = $this->getLogsHelper();
    			$helperLogs->saveLogs("admin");
    					
    					Mage::getSingleton("adminhtml/session")->addSuccess(
    							Mage::helper("adminhtml")->__("Item was successfully deleted"));
    					$this->_redirect("*/*/");
    		} catch (Exception $e) {
    			Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
    			$this->_redirect("*/*/edit", array(
    					"id" => $this->getRequest()
    					->getParam("id")
    			));
    		}
    	}
    	$this->_redirect("*/*/");
    }
    
    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction ()
    {
    	$fileName = 'piercingtiming.csv';
    	$grid = $this->getLayout()->createBlock('appointments/adminhtml_piercingtiming_grid');
    	$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    
    /**
     * Export order grid to Excel XML format
     */
    public function exportExcelAction ()
    {
    	$fileName = 'piercingtiming.xml';
    	$grid = $this->getLayout()->createBlock('appointments/adminhtml_piercingtiming_grid');
    	$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
    
    /**
     * return logs helper object
     */
    private function getLogsHelper(){
        return Mage::helper("appointments/logs");
    }
    
}