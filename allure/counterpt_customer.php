<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();

$name 	=  $_GET['file'];

if(empty($name))
	die("Please provide file path");

$app = Mage::app('default');
Mage::getSingleton('core/session', array('name' => 'adminhtml'));
Mage::app()->setCurrentStore(0);

$csvfile= Mage::getBaseDir('var').DS."counterpoint".DS.$name;
$csv = Array();
$rowcount = 0;
if (($handle = fopen($csvfile, "r")) !== FALSE) {
	$header = fgetcsv($handle);
	//echo "<pre>";
	//print_r($header);die;
	foreach($header as $c=>$_cols) {
		$header[$c] = strtolower(str_replace(" ","_",$_cols));
	}
	$header_colcount = count($header);
	while (($row = fgetcsv($handle)) !== FALSE) {
		$row_colcount = count($row);
		if ($row_colcount == $header_colcount) {
			$entry = array_combine($header, $row);
			if(!empty($entry['email_adrs_1'])){
				$csv[] = $entry;
			}
		}
		$rowcount++;
	}
	fclose($handle);
	Mage::log('completed parsing', Zend_Log::DEBUG,"counter_point_customer",true);
}
else {
	Mage::log('unable to read csv', Zend_Log::DEBUG,"counter_point_customer",true);
}


$resource     = Mage::getSingleton('core/resource');
$writeAdapter   = $resource->getConnection('core_write');

$present_count = 0;
$non_present_count = 0;
$model =Mage::getModel("customer/customer");

$websiteId = 1;
$store = 1;

//random string 6 characters for passwoard generation
$alphabets = range('A','Z');
$numbers = range('0','9');
$additional_characters = array('#','@','$');
$final_array = array_merge($alphabets,$numbers,$additional_characters);

try{
	$writeAdapter->beginTransaction();
	echo "<pre>";
	foreach ($csv as $data){
		$email = $data['email_adrs_1'];
		$customer = $model->loadByEmail($email);
		//var_dump($customer->getData());die;
		if($customer->getId()){
			Mage::log("Customer  ".$email." email is Present", Zend_Log::DEBUG,"counter_point_customer",true);
			$present_count += 1; 
		}else {
			Mage::log("Customer ".$email." email Not Present", Zend_Log::DEBUG,"counter_point_customer",true);
			$non_present_count += 1;
			//create new customer process
			$firstname = $data['fst_nam'];
			$lastname = $data['lst_nam'];
			$groupId = 1;
			
			$password = '';
			$length = 6;  //password length
			while($length--) {
				$key = array_rand($final_array);
				$password .= $final_array[$key];
			}
			Mage::log("password ".$password, Zend_Log::DEBUG,"counter_point_customer",true);
			//create customer
			try{
				$customer = Mage::getModel("customer/customer");
				$customer->setWebsiteId($websiteId)
					->setStoreId($store)
					->setGroupId($groupId)
					->setFirstname($firstname)
					->setLastname($lastname)
					->setEmail($email)
					->setPassword($password)
					->setCustomerType(1)  //counterpoint
					->save();
				Mage::log("New Customer Create email:".$email." Customer Id:".$customer->getId(), Zend_Log::DEBUG,"counter_point_customer",true);
			}catch (Exception $e){
				Mage::log("Customer Exception - ".$e->getMessage(),Zend_log::DEBUG,'counter_point_customer',true);
				$writeAdapter->rollback();
				Mage::log("Customer Rollbacked...",Zend_log::DEBUG,'counter_point_customer',true);
			}
			
			//create customer address
			if(!empty($data['adrs_1'])){
				$_custom_address = array (
						'firstname'  => $customer->getFirstname(),
						'lastname'   => $customer->getLastname(),
						'street'     => array (
								'0' => $data['adrs_1'],
								'1' => $data['adrs_2']." ".$data['adrs_3']
						),
						'city'       => $data['city'],
						'postcode'   => $data['zip_cod'],
						'country_id' => $data['cntry'],
						'region' 	=> 	$data['state'],
						'telephone'  => $data['phone_1'],
						'fax'        => $data['fax_1'],
				);
				
				$address = Mage::getModel("customer/address");
				$address->setData($_custom_address)
					->setCustomerId($customer->getId())
					->setIsDefaultBilling('1')
					->setIsDefaultShipping('1')
					->setSaveInAddressBook('1');
				$address->save();
				Mage::log("New Customer Address create.Customer Id:".$customer->getId()." Address Id:".$address->getId(), Zend_Log::DEBUG,"counter_point_customer",true);
			}
		}
		$customer = null;
	}
	$writeAdapter->commit();
	Mage::log("Present : ".$present_count." # Non Prsesnt : ".$non_present_count,Zend_log::DEBUG,'counter_point_customer',true);
}catch (Exception $e) {
	Mage::log("Exception - ".$e->getMessage(),Zend_log::DEBUG,'counter_point_customer',true);
	$writeAdapter->rollback();
	Mage::log("Rollbacked...",Zend_log::DEBUG,'counter_point_customer',true);
}

die("Operation finished...");


