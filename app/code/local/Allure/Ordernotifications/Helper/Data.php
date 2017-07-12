<?php
class Allure_Ordernotifications_Helper_Data extends Mage_Core_Helper_Abstract
{
	public  function getTimeSpanArray(){
		$timeSpan=array();
		$timeSpan['day']='Days';
		$timeSpan['week']='Weeks';
		return $timeSpan;
	}
	public function getTimeSpanName($timeSpan){
		$timeSpanLabel="";
		$timeSpanArray=$this->getTimeSpanArray();
		if(!empty($timeSpanArray)){
			foreach ($timeSpanArray as $key =>$value){
				if($timeSpan==$key){
					$timeSpanLabel=$value;
					break;
				}
			}
		}
		return $timeSpanLabel;
	}
}
	 