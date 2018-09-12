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
			$collection = Mage::getModel('catalog/product')->getCollection()
				->addAttributeToSelect('*')
				->addAttributeToFilter(array(array("attribute"=>"price","eq"=>0)))
				->addAttributeToFilter(array(array("attribute"=>"status","eq"=>1)));
				if (count($collection) > 0 && $status) {
					$helper->sendEmailAlertForProductPrice($collection);
				}
			}catch(Exception $e){
    		Mage::log($e->getMessage(),Zend_log::DEBUG,'allureAlerts.log',true);
    	}
			
	}

	public function alertSalesOfFour(){
		/* Get the collection */
		try{
			$helper = Mage::helper('alertservices');
			$status =	$this->getConfigHelper()->getEmailStatus();

			$currdate = Mage::getModel('core/date')->timestamp();
			$toDate	= date('Y-m-d H:i:s', $currdate);
			//$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 60 * 4);
			$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 15);
			$orders = Mage::getModel('sales/order')->getCollection()
				    ->addFieldToFilter('updated_at', array('from'=>$fromDate, 'to'=>$toDate))
				    ->addAttributeToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_COMPLETE))->setOrder('updated_at', 'ASC');
				    /*echo $orders->getSelect()->__toString();*/
				if (count($orders)<=0 && $status) {
					$helper->sendSalesOfFourEmailAlert();
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

			$currdate = Mage::getModel('core/date')->timestamp();
			$toDate	= date('Y-m-d H:i:s', $currdate);
			//$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 60 * 6);
			$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 15);

			$orders = Mage::getModel('sales/order')->getCollection()
				    ->addFieldToFilter('updated_at', array('from'=>$fromDate, 'to'=>$toDate))
				    ->addAttributeToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_COMPLETE))->setOrder('updated_at', 'ASC');
				    /*echo $orders->getSelect()->__toString();
				    var_dump(count($orders));*/
				if (count($orders)<=0 && $status) {
					$helper->sendSalesOfSixEmailAlert();
				}
		}catch(Exception $e){
    		Mage::log($e->getMessage(),Zend_log::DEBUG,'allureAlerts.log',true);
    	}
		
	}

	public function alertCheckoutIssue(){
		try{
			$helper = Mage::helper('alertservices');
			$status =	$this->getConfigHelper()->getEmailStatus();

			$currdate = Mage::getModel('core/date')->timestamp();
			$toDate	= date('Y-m-d H:i:s', $currdate);
			//$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 60 * 1);
			$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 10);

			$collection = Mage::getModel('alertservices/issues')->getCollection()
				->addFieldToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
				->addFieldToFilter('type',array('eq'=>'checkout'));
				//echo $collection->getSelect()->__toString();
				if (count($collection) >= 10 && $status) {
					$helper->sendCheckoutIssueAlert($collection);
				}
		}catch(Exception $e){
    		Mage::log($e->getMessage(),Zend_log::DEBUG,'allureAlerts.log',true);
    	}
			
	}
	
}