<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$logFile = "cntr_add_cust.log";

$fileName = $_GET["file"];
if(empty($fileName)){
    die("Please specify file name");
}

$csv = Mage::getBaseDir('var').DS."import".DS.$fileName;
try{
    $cnt    = 0;
    $io     = new Varien_Io_File();
    $io->streamOpen($csv, 'r');
    $resource       = Mage::getSingleton('core/resource');
    
    $writeAdapter   = $resource->getConnection('core_write');
    $writeAdapter->beginTransaction();
    
    $csvData = $io->streamReadCsv();
    /* echo "<pre>";
    print_r($csvData);die; */
    
    $arrCust = array();
    
    while($csvData = $io->streamReadCsv()){
        try{
            $custNo = $csvData[0];
            $name  = $csvData[1];
            $fstName = $csvData[3];
            $lstName = $csvData[5];
            $addr1 = $csvData[9];
            $addr2 = $csvData[10];
            $city = $csvData[12];
            $state = $csvData[13];
            $country = $csvData[15];
            $zipCode = $csvData[14];
            $phone = $csvData[16];
            $email1 = $csvData[22];
            $email2 = $csvData[23];
            $group = $csvData[177];
            $strId = $csvData[31];
            
            $arrCust[$custNo] = array(
                'cust_no'   =>$custNo,
                'name'      =>$name,
                'fst_name'  =>$fstName,
                'lst_name'  =>$lstName,
                'addr1'     =>$addr1,
                'addr2'     =>$addr2,
                'city'      =>$city,
                'state'     =>$state,
                'country'   =>$country,
                'zip_code'  =>$zipCode,
                'phone'     =>$phone,
                'email1'    =>$email1,
                'email2'    =>$email2,
                'group'     =>$group,
                'str_id'    =>$strId
            );
        }catch (Exception $e){
            Mage::log("csv read exc:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
        }
    }
    
    foreach ($arrCust as $key=>$value){
        try{
            /* if(empty($email1)){
                if(!empty($email2)){
                    $email1 = $email2;
                }else {
                    if(!empty($name)){
                        $email = str_replace(' ', '', $name);
                        $email = $email."@customers.mariatash.com";
                    }else{
                        if(!empty($fstName) && !empty($lstName)){
                            $email = $fstName.$lstName."@customers.mariatash.com";
                        }
                    }
                }
            } */
            
            $model = Mage::getModel("allure_teamwork/cpcustomer")->load($value['cust_no'],"cust_no");
            if(!$model->getId()){
                $model->setCustNo($value['cust_no'])
                ->setEmail($value['email1'])
                ->setOptionalEmail($value['email2'])
                ->setName($value['name'])
                ->setFstName($value['fst_name'])
                ->setLstName($value['lst_name'])
                ->setAddr1($value['addr1'])
                ->setAddr2($value['addr2'])
                ->setCity($value['city'])
                ->setState($value['state'])
                ->setZipCode($value['zip_code'])
                ->setPhone($value['phone'])
                ->setCountry($value['country'])
                ->setGroup($value['group'])
                ->setStrId($value['str_id'])
                ->save();
                Mage::log($cnt." add id:".$model->getId(),Zend_log::DEBUG,$logFile,true);
            
                if (($cnt % 500) == 0) {
                    $writeAdapter->commit();
                    $writeAdapter->beginTransaction();
                }
            
            }
            
            $model = null;
            Mage::log($cnt." exist id:".$model->getId(),Zend_log::DEBUG,$logFile,true);
            
        }catch (Exception $e){
            Mage::log("exc:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
        }
        $cnt++;
    }
    $writeAdapter->commit();
}catch (Exception $e){
    Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
}
Mage::log("Finish...",Zend_log::DEBUG,$logFile,true);
