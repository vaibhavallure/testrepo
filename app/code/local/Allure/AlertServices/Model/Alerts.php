<?php 
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
				$currdate = Mage::getModel('core/date')->timestamp();
				$toDate	= date('Y-m-d H:i:s', $currdate);
				$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 60 * 4);

				if ($debug) {
					echo "for 4 hours <br>";
					echo "to date <br>";
					var_dump($toDate).'<br>';
					echo "from date <br>";
					var_dump($fromDate).'<br>'; 
				}
				/*$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 15);*/
				$orders = Mage::getModel('sales/order')->getCollection()
					    ->addFieldToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
					    /*->addAttributeToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_COMPLETE))*/
					    ->setOrder('created_at', 'ASC');
					    /*echo $orders->getSelect()->__toString();*/
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
				$currdate = Mage::getModel('core/date')->timestamp();
				$toDate	= date('Y-m-d H:i:s', $currdate);
				$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 60 * 6);
				/*$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 15);*/
				$orders = Mage::getModel('sales/order')->getCollection()
					    ->addFieldToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
					    /*->addAttributeToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_COMPLETE))*/
					    ->setOrder('created_at', 'ASC');
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
				$currdate = Mage::getModel('core/date')->timestamp();
				$toDate	= date('Y-m-d H:i:s', $currdate);
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
	
}