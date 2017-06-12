<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();
$products = array() ;
$lower = $_GET['lower'];
$upper= $_GET['upper'];

if(empty($lower) || empty($upper)){
	die('Please add Upper and Lower limit');
}
	
$app = Mage::app('default');
Mage::getSingleton('core/session', array('name' => 'adminhtml'));
Mage::app()->setCurrentStore(0);

$resource     = Mage::getSingleton('core/resource');
$writeAdapter   = $resource->getConnection('core_write');
$table        = $resource->getTableName('catalog/product_super_attribute');

$collection = Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('entity_id',array('gteq' => $lower));
$collection->addAttributeToFilter('entity_id',array('lteq' => $upper));
$collection->addAttributeToFilter('type_id',array('eq' => 'configurable'));

//custom option metal color values
$atributeCode = 'metal_color';
$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$atributeCode);
$options = $attribute->getSource()->getAllOptions();

//website - stock array
$websitesCollection = Mage::getModel("core/website")->getCollection()
	->addFieldToFilter('stock_id',array('neq'=>0));
$websiteArr = array();
foreach ($websitesCollection as $website){
	$websiteArr[$website->getId()] = $website->getStockId();
}

$colorArr = array();
foreach ($options as $option){
	$val = explode(" ", $option['label']);
	$str = $option['label'];
	if(count($val)>2)
		$str = $val[0]." ".$val[count($val)-1];
	$colorArr[$option['value']]=$str;
}

foreach ($collection as $prod){
	$productId  = $prod->getId();
	$_product = Mage::getModel('catalog/product')->load($productId);
	$mainSku = $_product->getSku();
	$mainName = $_product->getName();
	
	$productAttributeOptions = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
	//print_r($productAttributeOptions);
	$childProducts = Mage::getModel('catalog/product_type_configurable')
		->getUsedProducts(null,$_product);
	
	$multipleOptions=array();
	
	if (count($_product->getOptions())>0){
		echo "Custom option already exists for product ID : ".$productId."<br>";
		Mage::log("Custom option already exists for product ID : ".$productId,Zend_Log::DEBUG,'allure_scripts',true);
	}else{
		foreach ($productAttributeOptions as $prodOptions){
			if($prodOptions['attribute_code']!='metal_color'){
				$optionsArray=array();
				foreach ($prodOptions['values'] as $optionsValues){
					$value=array(
							'title' => $optionsValues['label'],
							'price' => 00.00,
							'price_type' => 'fixed',
							'sku' => '',
							'sort_order' => 0,
					);
					array_push($optionsArray,$value);
				}
						
				$customOptions=array(
					'title' => $prodOptions['label'],
					'type' => 'drop_down',
					'is_required' => 1,
					'sort_order' => 0,
					'values' => $optionsArray
				);
				array_push($multipleOptions,$customOptions);
				$query	= "delete from {$table} WHERE product_super_attribute_id =".$prodOptions['id'];
				$writeAdapter->query($query);
				Mage::log("super attribute delete product id : ".$productId." super id : ".$prodOptions['id'],Zend_Log::DEBUG,'allure_scripts',true);
			}
		}
		
		echo "<pre>";
		$product = Mage::getModel('catalog/product')->load($productId);
		
		$optionInstance = $product->getOptionInstance()->unsetOptions();
		$product->setHasOptions(1);
		$optionInstance->setProduct($product);
		
		$product->setProductOptions($multipleOptions);
		$product->setCanSaveCustomOptions(true);
		
		$product->save();
		
		unset($product);
		
		Mage::log("Custom option Added for product Id : ".$productId,Zend_Log::DEBUG,'allure_scripts',true);
		echo $productId ." - Option Added successfully<br>";
	}
	
	$mainArr = array();
	foreach ($options as $prodOptions){
		$count = 0;
		$value = $prodOptions['value'];
		$inventory = array();
		$productId = 0;
		foreach ($childProducts as $child){
			if($value==$child->getMetalColor()){
				$count+=1;
				$websiteIds = $child->getWebsiteIds();
				foreach ($websiteIds as $websiteId){
					$stock = Mage::getModel('cataloginventory/stock_item')
					->loadByProductAndStock($child,$websiteArr[$websiteId]);
					$inventory[$websiteId] += $stock->getQty();
				}
				if($count==1){
					$productId = $child->getId();
				}
			}
		}
		if($productId!=0)
			$mainArr[$productId] = $inventory;
	}
	
	//var_dump($mainArr);
	
	foreach ($childProducts as $child){
		$productId = $child->getId();
		$product = Mage::getModel('catalog/product')->load($productId);
		$sku = "";
		$name = "";
		if(array_key_exists($productId, $mainArr)){
			$sku = $mainSku."|".$colorArr[$child->getMetalColor()];
			$name = $mainName."-".$colorArr[$child->getMetalColor()];
			$product->setName($name);
			$product->setSku($sku)->save();
			foreach ($mainArr[$productId] as $stockId=>$qty){
				$stock = Mage::getModel('cataloginventory/stock_item')
					->loadByProductAndStock($child,$stockId);
				$stock->setQty($qty)->save();
				Mage::log("product ID : ".$productId." Qty : ".$qty." updated",Zend_Log::DEBUG,'allure_scripts',true);
			}
		}else{
			$product->delete();
			Mage::log("Delete product ID : ".$productId,Zend_Log::DEBUG,'allure_scripts',true);
		}
	}
	
	Mage::log("Operation Successfull product Id : ".$productId,Zend_Log::DEBUG,'allure_scripts',true);
	
	echo "Operation Successfull<br>";
}
die;

