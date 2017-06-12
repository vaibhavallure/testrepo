<?php
class Allure_Appointments_Adminhtml_AppointmentpiercersController extends Mage_Adminhtml_Controller_Action
{

	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Appointments"));
	   $this->_addContent($this->getLayout()->createBlock('appointments/adminhtml_appointmentpiercers'));
	   $this->renderLayout();
    }
    
    public function gridAction()
    {
    	$this->loadLayout();
    	$this->getResponse()->setBody(
    			$this->getLayout()->createBlock('appointments/adminhtml_appointmentpiercers_grid')->toHtml()
    			);
    }
    
    public function editAction ()
    {
    	$this->_title($this->__("Edit Piercers"));
    
    	$id = $this->getRequest()->getParam("id");
    	$model = Mage::getModel('appointments/piercers')->load($id);
    	if ($model->getId()) {
    		Mage::register('appointment_piercers_data', $model);
    		$this->loadLayout();
    		$this->_setActiveMenu("allure/appointments");
    		$this->getLayout()
    		->getBlock("head")
    		->setCanLoadExtJs(true);
    		$this
    		->_addContent($this->getLayout()->createBlock('appointments/adminhtml_appointmentpiercers_edit'))
    		->_addLeft($this->getLayout()->createBlock('appointments/adminhtml_appointmentpiercers_edit_tabs'))
    		;
    				$this->renderLayout();
    	} else {
    		Mage::getSingleton("adminhtml/session")->addError(
    				Mage::helper("appointments")->__("Service does not exist."));
    		$this->_redirect("*/*/");
    	}
    }
    
    public function newAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$model = Mage::getModel('appointments/piercers')->load($id);
    
    	$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
    	if (!empty($data)) {
    		$model->setData($data);
    	}
    
    	$this->loadLayout();
    	//$this->_setActiveMenu('blog/posts');
    	$this->_title('Add new Piercer');
    
    	$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
    
    	$this
    	->_addContent($this->getLayout()->createBlock('appointments/adminhtml_appointmentpiercers_edit'))
    	->_addLeft($this->getLayout()->createBlock('appointments/adminhtml_appointmentpiercers_edit_tabs'))
    	;
    	$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
    	$this->renderLayout();
    }
    
    public function saveAction ()
    {
    	$post_data = $this->getRequest()->getPost();
    	if ($post_data) {
    
    		if(!$post_data['firstname']){
    			Mage::getSingleton("adminhtml/session")->addError(Mage::helper("appointments")->__("Firstname is required"));
    			//$this->_redirect("*/*/");
    			$this->_redirectReferer();
    			return;
    		}
    		//Check already email is exist
    		/* if($post_data['email']){
    			$email = $post_data['email'];
    			$store_id = $post_data['store_id'];
    			$piercer = Mage::getModel('appointments/piercers')->load($email,'email');//,$store_id,'store_id'
    			
    			if($piercer->getId()){
    				Mage::getSingleton("adminhtml/session")->addError(Mage::helper("appointments")->__("This Pierecer Email is Already is exist"));
    				$this->_redirectReferer();
    				return;
    			}    			
    		} */
    		try {    			
    			
    			/* logic for serialization */
    			$raw_timing_array = $post_data['working_hours']['value'];
    			$deleted_array = $post_data['working_hours']['delete'];
    			$timingData = array();
    			foreach ($raw_timing_array as $key=>$raw_field)
    			{
    				if($deleted_array[$key]=='')
    					$timingData[$key]=$raw_field;
    			}
    			$serializedTime = serialize($timingData);
    			unset($post_data['working_hours']);
    			$post_data['working_hours'] = $serializedTime;
    			
    			//Start of working days logic
    			$workdaysarr = explode(",", $post_data['working_days']);
    			$keys = array_keys($workdaysarr,' ');
				foreach ($keys as $key){
					unset($workdaysarr[$key]);
				}
				$post_data['working_days'] = implode(",", $workdaysarr);
				
				//END of working days logic
				
    			//$post_data['working_days'] = implode(',', $post_data['working_days']);
    			
    			$model = Mage::getModel('appointments/piercers')->addData($post_data)
    			->setId($this->getRequest()
    					->getParam("id"))
    					->save();
    
    					Mage::getSingleton("adminhtml/session")->addSuccess(
    							Mage::helper("adminhtml")->__("Piercer saved sucessfully"));
    					Mage::getSingleton("adminhtml/session")->setAppointmentpiercersData(false);
    
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
    			Mage::getSingleton("adminhtml/session")->setAppointmentpiercersData($this->getRequest()
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
    			$model = Mage::getModel('appointments/piercers');
    			$model->setId($this->getRequest()
    					->getParam("id"))
    					->delete();
    					Mage::getSingleton("adminhtml/session")->addSuccess(
    							Mage::helper("adminhtml")->__("Piercer was successfully deleted"));
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
    	$fileName = 'appointmentpiercers.csv';
    	$grid = $this->getLayout()->createBlock('appointments/adminhtml_appointmentpiercers_grid');
    	$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    
    /**
     * Export order grid to Excel XML format
     */
    public function exportExcelAction ()
    {
    	$fileName = 'appointmentpiercers.xml';
    	$grid = $this->getLayout()->createBlock('appointments/adminhtml_appointmentpiercers_grid');
    	$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
    
}