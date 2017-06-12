<?php
require_once('app/Mage.php'); 
umask(0);
Mage::app('admin');
$log_file = "allure_dataflow.log";
        
Mage::log("Dataflow Started", null, $log_file);
$profileId = 8;
Mage::log("profile started: " . $profileId . " at " . date("Y-m-d H:i:s"), null, $log_file);
$profile = Mage::getModel("dataflow/profile");
$userModel = Mage::getModel("admin/user");
$userModel->setUserId(0);
Mage::getSingleton("admin/session")->setUser($userModel);
$profile->load($profileId);
if (!$profile->getId()) {
    Mage::log("error: " . $profileId . " - incorrect profile id", null, $log_file);
    return;
}
Mage::register("current_convert_profile", $profile);
$profile->run();
Mage::log("profile ended: " . $profileId . " at " . date("Y-m-d H:i:s"), null, $log_file);
Mage::log("-----------------------------------", null, $log_file);

die;
        
$ipLocation = Mage::getModel('allure_geolocation/geoinfo');

$ipLocation->setData(array(
    'ip' => '66.65.83.127',
    'country' => 'US',
    'country_name' => 'United States',
    'region' => 'NY',
    'region_name' => 'New York',
    'city' => 'New York',
    'zip' => '10023',
    'timezone' => 'America/New_York',
    'isp' => 'Time Warner Cable',
    'lat' => '40.7769',
    'lon' => '-73.9813',
    'created_at' => '2017-01-23 00:00:00'
));
var_dump($ipLocation->getData());
$ipLocation->save();


$ipCollection = $ipLocation->getCollection();

foreach ($ipCollection as $ipLocation) {
    var_dump($ipLocation->getData());
}

var_dump($ipLocations->count());
die;

$products = array() ;
$uname = "allureinc";
$pwd = "12qwaszx";
$user = $_GET['user'];
$pass = $_GET['pass'];
if((isset($user)&&!empty($user)) && (isset($pass)&&!empty($pass))){
	if($user==$uname && $pass==$pwd){
		$_opeartionType = $_GET['type'];
		
		if(isset($_opeartionType) && !empty($_opeartionType)){
			if($_opeartionType=="price"){
				$_rangeProduct = $_GET['product_range'];
				$_store 	   = $_GET['store'];
				if((isset($_rangeProduct) && !empty($_rangeProduct) 
							&& (isset($_store) && !empty($_store)))) {
					$_rangeProduct = explode(",", $_rangeProduct);
					if(count($_rangeProduct)==2){
						$startProduct = $_rangeProduct[0];
						$lastProduct = $_rangeProduct[1];
						$productShareObj = Mage::getModel('productshare/observer');
						$productShareObj->updatePriceByStoreProduct($_store,$startProduct,$lastProduct);
					}
				}
			}
		}else{
			print_r("Invalid Operation.");
		}
	
	}else{
		print_r("Invalid username and passwaord.");
	}
	
}else{
	print_r("Please Provide Credentials.");
}

die;


if($ch==1){
	Mage::getModel('productshare/observer')->shareAvailableProductsToStoreRun();

}else {
	if (isset($_GET['products']) && !empty($_GET['products']))
		$products = explode(',', $_GET['products']);
		$website = $_GET['website'];
		$storeId = $_GET['store'];
		Mage::getModel('productshare/observer')->shareAvailableProductsToStoreAdditional($products,$storeId,$website);
	
}
die;