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
						echo "<br>order count : ".count($orders);
					}
					
					if (count($orders) <=0 ) {
						if ($debug) {
							var_dump('zero order')
						}
						//$lastorder = Mage::getModel('sales/order')->getCollection()->getLastItem(); 
						$helper->sendSalesOfFourEmailAlert('$lastorder->getCreatedAt()');
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
						$lastorder = Mage::getModel('sales/order')->getCollection()->getLastItem();
						$helper->sendSalesOfSixEmailAlert($lastorder->getCreatedAt());
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
					}
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
			$configPath = $this->getConfigHelper()->getAvgLoadTimePath();
			$timeArray = $this->getConfigHelper()->getAvgLoadTimeArray();

				if ($status) {
					$ch = curl_init("https://www.mariatash.com/");
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$res = curl_exec($ch);
					$info = curl_getinfo($ch);
					$avg_time = number_format((float)$info['total_time'], 2);
					if ($avg_time) {
						if (is_null($timeArray) || !$timeArray) {
							Mage::getModel('core/config')->saveConfig($configPath,$avg_time);
						}else{
							$timearray = explode(',', $timeArray);
							if(count($timearray) < 7){
								array_push($timearray,$avg_time);

								$newAvgValure = implode(',', $timearray);
								Mage::getModel('core/config')->saveConfig($configPath,$newAvgValure);
							}
							if(count($timearray) == 7){
								$totAvgTime = (array_sum($timearray))/7;
								array_shift($timearray);
								array_push($timearray,$avg_time);

								$newAvgValure = implode(',', $timearray);
								Mage::getModel('core/config')->saveConfig($configPath,$newAvgValure);
								/*$totAvgTime = 30;*/
								if ($totAvgTime >= 30) {
									$helper->sendEmailAlertForAvgPageLoad($totAvgTime);
								}else{
									Mage::log($totAvgTime.' is not > 30',Zend_log::DEBUG,'allureAlerts.log',true);
								}
							}
						}
					}
				}
			}catch(Exception $e){
    		Mage::log($e->getMessage(),Zend_log::DEBUG,'allureAlerts.log',true);
    	}				
	}
	
}