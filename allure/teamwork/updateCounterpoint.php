<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$logFile = "tw_update_eid.log";
$logFileError = "tw_update_eid_error.log";
$cusNoIndex = 0;

$emailIndex = 22;
$emailIndex2= 23;
$fstIndex=3;
$lstIndex=5;
$nameIndex=2;

$productModel = Mage::getSingleton('catalog/product');
$csv = Mage::getBaseDir('var').DS."teamwork".DS.'AR_CUST.csv';
$io = new Varien_Io_File();
$io->streamOpen($csv, 'r');
$counter=0;
while($csvData = $io->streamReadCsv()){
    if (count($csvData) < 2) {
        continue;
    }
    $email="";
    $custNo = trim($csvData[$cusNoIndex]);
    $email1 = trim($csvData[$emailIndex]);
    $email2 =trim($csvData[$emailIndex2]);
    $fstName=trim($csvData[$fstIndex]);
    $lstName=trim($csvData[$lstIndex]);
    $name=trim($csvData[$nameIndex]);
    
    
    
    if(empty($email1)){
        if(!empty($email2)){
            $email = $email2;
        }else {
          
                if(!empty($name)){
                    $email = str_replace(' ', '', $name);
                    if(preg_match("/OL/", $custNo)){
                        $custNum = str_replace('-', '', $custNo);
                        $email = $email.$custNum;
                    }
                }else{
                    if(!empty($fstName)|| !empty($lstName)){
                        $email = $fstName.$lstName;
                        if(preg_match("/OL/", $custNo)){
                            $custNum = str_replace('-', '', $custNo);
                            $email = $email.$custNum;
                     }
                }
            }
            $email = strtolower($email."@customers.mariatash.com");
        }
    }else{
        $email = $email1;
    }
      
    $customer = Mage::getModel("customer/customer");
    $customer->setWebsiteId(Mage::app()->getWebsite('admin')->getId());
    $customer->loadByEmail($email);
    
    if($customer->getId())   {
        if($customer->getCounterpointCustNo()!=$custNo){
            echo $counter.'----'.$custNo.'-'.$email;
            $customer->setCounterpointCustNo($custNo);
            $customer->save();
            echo '<br>';
            $counter++;
        }
        
    }
    
}
echo "Final Count:".$counter;
die("Finish");