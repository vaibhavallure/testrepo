<?php
require_once Mage::getBaseDir().'/allure/alrGoogleAnalytics.php';

class Allure_AlertServices_Model_Alerts
{	
	private function getConfigHelper(){
        return Mage::helper("alertservices/config");
    }

	public function alertProductPrice(){
			try{
				$helper = Mage::helper('alertservices');

				$status =	$this->getConfigHelper()->getEmailStatus();
					if ($status) {
						$collection = Mage::getModel('catalog/product')->getCollection()
						->addAttributeToSelect('*')
						->addAttributeToFilter(array(array("attribute"=>"price","eq"=>0)))
						->addAttributeToFilter(array(array("attribute"=>"sku","neq"=>'gift')))
						->addAttributeToFilter(array(array("attribute"=>"status","eq"=>1)));
						if (count($collection) > 0) {
							$helper->sendEmailAlertForProductPrice($collection);
						}
					}
				}catch(Exception $e){
	    		Mage::log($e->getMessage(),Zend_log::DEBUG,'allureAlerts.log',true);
	    	}
				
		}

	public function alertSalesOfFour($debug = false){
		/* Get the collection */
		try{
			$helper = Mage::helper('alertservices');
			$status =	$this->getConfigHelper()->getEmailStatus();

			if ($status) {
				$currdate = Mage::getModel('core/date')->gmtDate();
				$toDate	= $currdate;
				$fromDate = date('Y-m-d H:i:s', strtotime($currdate) - 60 * 60 * 4);

				if ($debug) {
					echo "for 4 hours <br>";
					echo "to date <br>";
					var_dump($toDate).'<br>';
					echo "from date <br>";
					var_dump($fromDate).'<br>'; 
				}
				$orders = Mage::getModel('sales/order')->getCollection()
						  ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
						  ->addAttributeToSelect('*');
					if ($debug) {
						echo $orders->getSelect()->__toString();
						var_dump(count($orders)); //die();
					}
					if (count($orders) <=0 ) {
						$helper->sendSalesOfFourEmailAlert();
					}
			}
			
		}catch(Exception $e){
    		Mage::log($e->getMessage(),Zend_log::DEBUG,'allureAlerts.log',true);
    	}
		
	}

	public function alertSalesOfSix(){
		/* Get the collection */
		try{
			$helper = Mage::helper('alertservices');
			$status =	$this->getConfigHelper()->getEmailStatus();
			if ($status) {
				$currdate = Mage::getModel('core/date')->gmtDate();
				$toDate	= $currdate;
				$fromDate = date('Y-m-d H:i:s', strtotime($currdate) - 60 * 60 * 6);
				/*$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 15);*/
				$orders = Mage::getModel('sales/order')->getCollection()
						  ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
						  ->addAttributeToSelect('*');
					    /*echo $orders->getSelect()->__toString();*/
					if (count($orders)<=0) {
						$helper->sendSalesOfSixEmailAlert();
					}
			}
		}catch(Exception $e){
    		Mage::log($e->getMessage(),Zend_log::DEBUG,'allureAlerts.log',true);
    	}
		
	}

	public function alertCheckoutIssue(){
		try{
			$helper = Mage::helper('alertservices');
			$status =	$this->getConfigHelper()->getEmailStatus();
			if ($status) {
				$currdate = Mage::getModel('core/date')->gmtDate();
				$toDate	= $currdate;
				$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 60 * 1);
				/*$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 10);*/

				$collection = Mage::getModel('alertservices/issues')->getCollection()
					->addFieldToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
					->addFieldToFilter('type',array('eq'=>'checkout'));
					//echo $collection->getSelect()->__toString();
					//change count to 10 on live
					if (count($collection) >= 10 && $status) { 
						$helper->sendCheckoutIssueAlert($collection);
					}				# code...
			}
		}catch(Exception $e){
    		Mage::log($e->getMessage(),Zend_log::DEBUG,'allureAlerts.log',true);
    	}
			
	}

	public function alertNullUsers(){
		try{
			$helper = Mage::helper('alertservices');

			$status =	$this->getConfigHelper()->getEmailStatus();
				if ($status) {
					$currdate = Mage::getModel('core/date')->gmtDate();
					$lastHour = date('H', strtotime($currdate) - 60 * 60 * 1);
					$analytics = initializeAnalytics();
					$response = getUsersReport($analytics);
					$users = getResults($response,$lastHour,'users');
					/*var_dump($users);*/
					if (count($users) <= 0) {
						$helper->sendEmailAlertForNullUsers();
					}
				}
			}catch(Exception $e){
    		Mage::log($e->getMessage(),Zend_log::DEBUG,'allureAlerts.log',true);
    	}
				
	}

	public function alertPageNotFound(){
		try{
			$helper = Mage::helper('alertservices');

			$status =	$this->getConfigHelper()->getEmailStatus();
				if ($status) {
					$analytics = initializeAnalytics();
					$response = getPageReport($analytics);
					$pageReport = getResults($response,null,'page');
					if (count($pageReport) > 0) {
						$helper->sendEmailAlertForPageNotFound($pageReport);
					}
				}
			}catch(Exception $e){
    		Mage::log($e->getMessage(),Zend_log::DEBUG,'allureAlerts.log',true);
    	}				
	}

	public function alertAvgPageLoad(){
		try{
			$helper = Mage::helper('alertservices');

			$status =	$this->getConfigHelper()->getEmailStatus();
				if ($status) {
					$collection =1;
					if (count($collection) > 0) {
						$helper->sendEmailAlertForAvgPageLoad($collection);
					}
				}
			}catch(Exception $e){
    		Mage::log($e->getMessage(),Zend_log::DEBUG,'allureAlerts.log',true);
    	}				
	}
	
}