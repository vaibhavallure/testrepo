<?php

class Allure_Piercing_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function checkSubstring($string,$subString){
		/* if(strpos(strtolower($string), strtolower("#shopby"))!=false || strpos(strtolower($string), strtolower("#shop by"))!=false)
			return (strpos(strtolower($string), strtolower($subString) ) !== false )?true:false; */
		if(preg_match("/#shopby/",strtolower($string)) || preg_match("/#shop by/",strtolower($string)))
			return (preg_match("/".$subString."/",strtolower($string))) ?true:false;
		return false;
	}
	
	public function checkContent($text){
		
	}
	
	public function getShopCat($data){
		$str = "";
		//print_r($data);echo "<br>";
		/* if(strpos(strtolower($data), strtolower("#shopby"))!=false){
			$str = substr($data, strpos($data, "#shopby") + 8);
		}else if(strpos(strtolower($data), strtolower("#shop by"))!=false){
			$str = substr($data, strpos($data, "#shop by") + 9);
		} */
		
		if(preg_match("/#shopby/",strtolower($data))){
			$str = substr($data, strpos($data, "#shopby") + 8);
		}else if(preg_match("/#shop by/",strtolower($data))){
			$str = substr($data, strpos($data, "#shop by") + 9);
		}
		
		//print_r($str);echo "<br><br>";
		return $str;
	}
	
	public function getProducts($key){
		$collection = $this->getShopCollection();
		$arraypr = array();
		foreach ($collection as $feed){
			if($this->checkSubstring($feed->getCaption(),$key)){
				if($feed->getProductCount()>0)
					$arraypr[] = $feed->getData();//array('id'=>$feed->getId(),'text'=>$feed->getCaption());
			}
		}
		return $arraypr;
	}
	
	public function getShopCollection(){
		$collection = Mage::getResourceModel('allure_instacatalog/feed_collection')
		->addFieldToFilter('status', 1)
		->addFieldToFilter('lookbook_mode', 1)
		->addFieldToFilter('product_count', array('gt'=>0));
		return $collection;
	}
}