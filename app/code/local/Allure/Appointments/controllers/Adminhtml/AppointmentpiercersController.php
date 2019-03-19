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
    protected function _isAllowed()
    {
    	return true;
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

    		try {

    			/* logic for serialization */
    			$raw_timing_array = $post_data['working_hours']['value'];
    			$deleted_array = $post_data['working_hours']['delete'];
    			$timingData = array();

    			foreach ($raw_timing_array as $key => $raw_field) {

    				if ($deleted_array[$key]=='')
    					$timingData[$key] = $raw_field;
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

    			$model = Mage::getModel('appointments/piercers')->addData($post_data)
    			->setId($this->getRequest()
    					->getParam("id"))
    					->save();

    			//add logs
    			$helperLogs = $this->getLogsHelper();
    			$helperLogs->saveLogs("admin");

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

    			//add logs
    			$helperLogs = $this->getLogsHelper();
    			$helperLogs->saveLogs("admin");

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
    public function viewCalenderAction ()
    {
    	$post_data = $this->getRequest()->getParam('piercer');
    	$store_id = 0;
    	if($post_data){
    		$piercers_id = $post_data;
    	}

    	Mage::register('piercers_id', $piercers_id);

    	$this->loadLayout();
    	$this->_addContent($this->getLayout()->createBlock('appointments/adminhtml_appointmentpiercers_calenderview'));
    	$this->renderLayout();

    }
    public function calendereventsAction(){
    	$calenderEvents = array();

    	/* $name = "test name";

    	$time = strtotime('3/16/2017 3:28 AM');

    	$newformat = date('Y-m-d H:i',$time);

    	$start_time = $newformat;
    	$end_time = $newformat;
    	$url = "not found"; */
    	$piercer_id=0;
    	$piercer_id = $this->getRequest()->getParam('piercer_id');
    	Mage::log($piercer_id,Zend_Log::DEBUG, 'appointments', true );
    	$url = "not found";
  /*   	$allAppointments = Mage::getModel('appointments/appointments')->getCollection();
    	$allAppointments->addFieldToFilter('app_status',array('in'=>array('1','2')));

    	if(!empty($piercer_id) && $piercer_id!=0)
    		$allAppointments->addFieldToFilter('piercer_id',$piercer_id);

    	if($allAppointments){

    		foreach ($allAppointments as $appointment){
    			$calenderEvents[] = array('title'=>$appointment->getFirstname()." ".$appointment->getLastname(),
    					'start'=>$appointment->getAppointmentStart(),
    					'end'=>$appointment->getAppointmentEnd(),
    					'url'=>$this->getUrl('admin_appointments/adminhtml_appointments/view/id/'.$appointment->getId(),array('_secure' => true))
    			);
    		}
    	} */
    	$helper=Mage::helper('appointments');
    	$piercers = Mage::getModel('appointments/piercers')->getCollection();
    	$piercers->addFieldToFilter('is_active',array('in'=>array('1')));
    	if(!empty($piercer_id) && $piercer_id!=0)
    	    $piercers->addFieldToFilter('id',$piercer_id);
    	$calenderEvents=array();
    	foreach ($piercers as $piercer){
    	    $color=$piercer->getColor();
    	    Mage::log($color,Zend_Log::DEBUG,'abc',true);
    	    $workdaysarr = explode(",", $piercer->getWorkingDays());
    	    if(count($workdaysarr)){
    	        foreach ($workdaysarr as $singeDay){
    	            $dayOfWeek = date("d", strtotime($singeDay));
    	            $day = date('l', strtotime($singeDay));
    	            $workingHours = $piercer->getWorkingHours();
    	            $workingHours = unserialize($workingHours);
    	            foreach ($workingHours as $workSlot)
    	            {
    	                //$workStart = $workSlot['start'].":00";

    	                if($workSlot['day']!=$day){
    	                    continue;

    	                }
    	                $start=$helper->decimalToTime($workSlot['start']);
    	                $start = date("Y-m-d", strtotime($singeDay))." " .$start;
    	                 Mage::log($start,Zend_Log::DEBUG,'abc',true);
    	                 $breakStart=$helper->decimalToTime($workSlot['break_start']);
    	                 $breakStart = date("Y-m-d", strtotime($singeDay))." " .$breakStart;

    	                $calenderEvents[] = array('title'=>$piercer->getFirstname()." ".$piercer->getLastname(),
    	                    'start'=>$start,
    	                    'end'=>$breakStart,
    	                    'url'=>$this->getUrl('admin_appointments/adminhtml_appointmentpiercers/edit/id/'.$piercer->getId(),array('_secure' => true)),
    	                    'color'=>$color

    	                );

    	                $breakEnd=$helper->decimalToTime($workSlot['break_end']);
    	                $end=$helper->decimalToTime($workSlot['end']);

    	                $breakEnd = date("Y-m-d", strtotime($singeDay))." " .$breakEnd;

    	                $end = date("Y-m-d", strtotime($singeDay))." " .$end;
    	                $calenderEvents[] = array('title'=>$piercer->getFirstname()." ".$piercer->getLastname(),
    	                    'start'=>$breakEnd,
    	                    'end'=>$end,
    	                    'url'=>$this->getUrl('admin_appointments/adminhtml_appointmentpiercers/edit/id/'.$piercer->getId(),array('_secure' => true)),
    	                    'color'=>$color
    	                );
    	                $breakColor="#D08040";
    	                $calenderEvents[] = array('title'=>"Lunch Break"." - ".$piercer->getFirstname()." ".$piercer->getLastname(),
    	                    'start'=>$breakStart,
    	                    'end'=>$breakEnd,
    	                    'url'=>$this->getUrl('admin_appointments/adminhtml_appointmentpiercers/edit/id/'.$piercer->getId(),array('_secure' => true)),
    	                    'color'=>$breakColor

    	                );
    	            }
    	        }

    	    }


    	}
    	$_currentStore=Mage::app()->getStore();
    	$code1 = $_currentStore->getCode();
    	$lanCode = substr(strrchr($code1, "_"), 1);

    	$response = array('status'=>true,'events'=>$calenderEvents,'lang'=>$lanCode);
    	$jsonData = json_encode ( compact ( 'success', 'response', 'data' ) );
    	$this->getResponse ()->setHeader ( 'Content-type', 'application/json' );
    	$this->getResponse ()->setBody ( $jsonData );
    }

    /**
     * return logs helper object
     */
    private function getLogsHelper(){
        return Mage::helper("appointments/logs");
    }

}
