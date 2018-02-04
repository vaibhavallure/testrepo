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

die;

$csv = Mage::getBaseDir('var').DS."import".DS.$fileName;
try{
    $cnt    = 0;
    /* $io     = new Varien_Io_File();
    $io->streamOpen($csv, 'r'); */
    $resource       = Mage::getSingleton('core/resource');
    
    $writeAdapter   = $resource->getConnection('core_write');
    $writeAdapter->beginTransaction();
    
    /* $csvData = $io->streamReadCsv();
    echo "<pre>";
    print_r($csvData);die; */
    
    $arrCust = array();
    
    
    $csvA = Array();
    $rowcount = 0;
    if (($handle = fopen($csv, "r")) !== FALSE) {
        $max_line_length =  15000;
        $header = fgetcsv($handle, $max_line_length);
        foreach($header as $c=>$_cols) {
            $header[$c] = strtolower(str_replace(" ","_",$_cols));
        }
        $header_colcount = count($header);
        while (($row = fgetcsv($handle, $max_line_length)) !== FALSE) {
            $row_colcount = count($row);
            if ($row_colcount == $header_colcount) {
                $entry = array_combine($header, $row);
                $csvA[] = $entry;
            }
            else {
                Mage::log("no match",Zend_log::DEBUG,$logFile,true);
            }
            $rowcount++;
        }
    }
    
    /* echo "<pre>";
    print_r($csvA);die; */
    
    
    /* while($csvData = $io->streamReadCsv()){
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
    } */
    
    
    /* echo "<pre>";
    print_r($arrCust);die; */
    foreach ($csvA as $key=>$value){
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
                $phone = (!empty($value['phone_1']))?$value['phone_1']:$value['phone_2'];
                $model->setCustNo($value['cust_no'])
                ->setEmail($value['email_adrs_1'])
                ->setOptionalEmail($value['email_adrs_2'])
                ->setName($value['nam'])
                ->setFstName($value['fst_nam'])
                ->setLstName($value['lst_nam'])
                ->setAddr1($value['adrs_1'])
                ->setAddr2($value['adrs_2'])
                ->setCity($value['city'])
                ->setState($value['state'])
                ->setZipCode($value['zip_cod'])
                ->setPhone($phone)
                ->setCountry($value['cntry'])
                ->setGroup($value['cust_nam_typ'])
                ->setStrId($value['str_id'])
                ->save();
                Mage::log($cnt." add id:".$model->getId(),Zend_log::DEBUG,$logFile,true);
            
                if (($cnt % 500) == 0) {
                    $writeAdapter->commit();
                    $writeAdapter->beginTransaction();
                }
            
            }
            
            Mage::log($cnt." exist id:".$model->getId(),Zend_log::DEBUG,$logFile,true);
            $model = null;
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
