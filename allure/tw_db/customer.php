<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

$startDate = $_GET['start'];
$endDate   = $_GET['end'];

/* if(empty($startDate)){
    die("Please mention data in 'start' field.");
}else{
    $startDate1 = DateTime::createFromFormat('Y-m-d', $startDate);
    $date_errors = DateTime::getLastErrors();
    if ($date_errors['warning_count'] + $date_errors['error_count'] > 0) {
        die("'start' field format wrong. eg:2009-01-30");
    }
}

if(empty($endDate)){
    die("Please mention data in 'end' field.");
}else{
    $endDate1 = DateTime::createFromFormat('Y-m-d', $endDate);
    $date_errors = DateTime::getLastErrors();
    if ($date_errors['warning_count'] + $date_errors['error_count'] > 0) {
        die("'end' field format wrong. eg:2009-01-30");
    }
} */



function getQuery(){
    $query = "SELECT distinct C.CustomerId, C.FirstName, C.LastName, C.EMail1, C.EMail2, 
            C.RecModified, C.Address1, C.Address2, C.City, C.State, C.PostalCode, 
            C.Phone1, C.Phone2, C.Phone3, C.CustomFlag1, 
            C.AcceptMarketing, C.AcceptTransactional1,
            CTR.CODE  
            FROM CUSTOMER_T AS C LEFT JOIN COUNTRY_T AS CTR 
            ON C.COUNTRYID = CTR.COUNTRYID
            join receipt r on r.SellToCustomerId = C.CustomerId
            where r.WebOrderNo is null
            and r.EmailAddress is not null 
            and r.StateDate >= '2018-02-02' and r.StateDate < '2018-02-03'
            -- WHERE C.RecModified >= '2018-02-01' AND C.RecModified <= '2018-02-05'
            ; 
            ";
    return $query;
}

function getConnection(){
    $host_name  = "cpsql";
    $db_user    = "root";
    $db_pass    = "12qwaszx";
    
    $conn = odbc_connect($host_name, $db_user,$db_pass);
    if($conn){
        echo "Connection established.<br>";
    }else{
        echo "Connection not established.<br>";
        $conn = null;
    }
    return $conn;
}

//function processQuery(){
    $log_file = "abc.log";
    
    $connection = getConnection();
    if($connection != null){
        $query = getQuery();
        
        /* $table_columns = array(
            "CustomerId",
            "FirstName",
            "LastName",
            "EMail1",
            "EMail2",
            "RecModified",
            "Address1",
            "Address2",
            "City",
            "State",
            "PostalCode",
            "CODE",
            "Phone1",
            "Phone2",
            "Phone3"
        ); */
        
        
        $header = array(
            "teamwork_customer_id"=>"teamwork_customer_id",
            "firstname"=>"firstname",
            "lastname"=>"lastname",
            "email"=>"email",
            "is_wholesale"=>"is_wholesale",
            "created_at"=>"created_at",
            "street"=>"street",
            "city"=>"city",
            "state"=>"state",
            "country"=>"country",
            "postal_code"=>"postal_code",
            "phone"=>"phone",
            "accept_marketing"=>"accept_marketing",
            "accept_transactional"=>"accept_transactional"
        );
        
        try{
            
            $folderPath   = Mage::getBaseDir("var") . DS . "teamwork" . DS . "customer";
            $filename     = "CUSTOMER_".$startDate."-To-".$endDate.".csv";
            $filepath     = $folderPath . DS . $filename;
            
            
            $io = new Varien_Io_File();
            $io->setAllowCreateFolders(true);
            $io->open(array("path" => $folderPath));
            
            $csv = new Varien_File_Csv();
            //$csv->saveData($filepath,$header);
            
            $result = odbc_exec($connection, $query);
            $rowData = array();
            $rowData[]  = $header;
            while (odbc_fetch_row($result)){
                /* for ($i = 1; $i <= odbc_num_fields($result); $i++){
                    $column_name  = odbc_field_name($result, $i);
                    $column_value = odbc_result($result, $column_name);
                    var_dump($column_name . " : " . $column_value);
                } */
                $teamworkId = odbc_result($result, "CustomerId");
                $firstName = odbc_result($result, "FirstName");
                $lastName = odbc_result($result, "LastName");
                $email1 = odbc_result($result, "EMail1");
                $email2 = odbc_result($result, "EMail2");
                $createdAt = odbc_result($result, "RecModified");
                $address1 = odbc_result($result, "Address1");
                $address2 = odbc_result($result, "Address2");
                $city = odbc_result($result, "City");
                $state = odbc_result($result, "State");
                $postalCode = odbc_result($result, "PostalCode");
                $country = odbc_result($result, "CODE");
                $phone1 = odbc_result($result, "Phone1");
                $phone2 = odbc_result($result, "Phone2");
                $phone3 = odbc_result($result, "Phone3");
                $isWholesale = odbc_result($result, "CustomFlag1");
                
                $acceptMarketing = odbc_result($result, "AcceptMarketing");
                
                $acceptTransactional = odbc_result($result, "AcceptTransactional1");
                
                $row = array();
                $row["teamwork_customer_id"] = $teamworkId;
                $row["firstname"] = $firstName;
                $row["lastname"] = $lastName;
                $row["email"] = ($email1) ? $email1 : $email2;
                $row["is_wholesale"] = $isWholesale;
                $row["created_at"] = $createdAt;
                $row["street"] = ($address1)? ($address2) ? $address1. ", ".$address2 :"" : "";
                $row["city"] = $city;
                $row["state"] = $state;
                $row["country"] = $country;
                $row["postal_code"] = $postalCode;
                $row["phone"] = ($phone1)?$phone1:$phone2;
                
                $row["accept_marketing"] = $acceptMarketing;
                $row["accept_transactional"] = $acceptTransactional;
                
                
                $rowData[] = $row;
            }
            $csv->saveData($filepath,$rowData);
           //print_r($rowData);
            //$action = new Allure_Salesforce_Adminhtml_GenController();
            //$content = array("type" => "filename", "value" => $filepath);
            //$action->_prepareDownloadResponse($filename, $content);
            
            //header('Content-type: text/csv');
            //header('Content-Disposition: attachment; filename="'.$filename.'"');
            
        }catch (Exception $e){
            echo $e->getMessage();
            Mage::log("Exception:".$e->getMessage(),Zend_Log::DEBUG,$log_file,true);
        }
    }
//}

//processQuery();

die("Finish...");



