<?php
class Allure_AlertServices_Helper_Data extends Mage_Core_Helper_Abstract
{

    private function getConfigHelper(){
        return Mage::helper("alertservices/config");
    }

	public function sendEmailAlertForProductPrice($collection){
    	try{		
    		$templateId = $this->getConfigHelper()
            ->getProductPriceEmailTemplate();

    		$emailTemplate = Mage::getModel('core/email_template');
            $storeId = Mage::app()->getStore()->getId();
            $senderName = $this->getConfigHelper()->getAlertSenderName();
            $senderEmail = $this->getConfigHelper()->getAlertSenderEmail();

            $sender = array('name' => $senderName,
                            'email' => $senderEmail);
            $recieverEmails = $this->getConfigHelper()->getEmailsGroup();
            $recieverNames = $this->getConfigHelper()->getEmailGroupNames();

            $recipientEmails = explode(',',$recieverEmails);
            $recipientNames = explode(',',$recieverNames);
           
            //$emailTemplateVariables['collection'] = $collection;
            $emailTemplateVariables['store_name'] = Mage::app()->getStore()->getName();
        	$emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        	$header = array(
                "product_name"=>"Product_Name",
                "sku"=>"SKU",
             	"price"=>"Price"
            );

             $folderPath   = Mage::getBaseDir("var") . DS . "alerts" . DS . "product_price";
             $date = date('Y-m-d');
             $filename     = "PRODUCT_PRICE_".$date.".csv";            
             $filepath     = $folderPath . DS . $filename;

             $io = new Varien_Io_File();
             $io->setAllowCreateFolders(true);
             $io->open(array("path" => $folderPath));
             $csv = new Varien_File_Csv();

             $rowData = array();
             $rowData[] = $header;
             foreach ($collection as $product) {
             	$row = array();
             	$row["product_name"] = $product->getName();
                    $row["sku"] = $product->getSku();
                    $row["price"] = 0;//$product->getPrice();
                    $rowData[] = $row;
             }            

     		$csv->saveData($filepath,$rowData);

    		if ($templateId) {
    			$emailTemplate->getMail()->createAttachment(
    				file_get_contents($filepath),
    				Zend_Mime::TYPE_OCTETSTREAM,
    				Zend_Mime::DISPOSITION_ATTACHMENT,
    				Zend_Mime::ENCODING_BASE64,
    				$filename
    			);

                $emailTemplate->sendTransactional(
                	$templateId,
                	$sender,
                	$recipientEmails, //here comes recipient emails
                	$recipientNames, // here comes recipient names
                	$emailTemplateVariables,
                	$storeId
                );
              }
             
    		}catch(Exception $e){
                $this->alr_alert_log($e->getMessage(),'allureAlerts.log');
        	}
	}

	public function sendSalesOfEmailAlert($lastOrderdate,$hourReport){
    	try{		
    		$templateId = $this->getConfigHelper()->getSaleEmailTemplate();

    		$emailTemplate = Mage::getModel('core/email_template');
            $storeId = Mage::app()->getStore()->getId();
            $senderName = $this->getConfigHelper()->getAlertSenderName();
            $senderEmail = $this->getConfigHelper()->getAlertSenderEmail();

            $sender = array('name' => $senderName,
                            'email' => $senderEmail);

            if ($hourReport == 4 || $hourReport == 6) {
                $recieverEmails = $this->getConfigHelper()->getEmailsGroup();
                $recieverNames = $this->getConfigHelper()->getEmailGroupNames();
            }elseif ($hourReport == 2) {
                $recieverEmails = $this->getConfigHelper()->getTestEmailsGroup();
                $recieverNames = $this->getConfigHelper()->getTestEmailGroupNames();
            }

            $recipientEmails = explode(',',$recieverEmails);
            $recipientNames = explode(',',$recieverNames);
           
            $emailTemplateVariables['store_name'] = Mage::app()->getStore()->getName();
        	$emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            $emailTemplateVariables['hour_alert'] = $hourReport;
            $emailTemplateVariables['last_order_date'] = Mage::getModel('core/date')->date("F j, Y \a\\t g:i a",$lastOrderdate);
    		if ($templateId) {    			
                $emailTemplate->sendTransactional(
                	$templateId,
                	$sender,
                	$recipientEmails, //here comes recipient emails
                	$recipientNames, // here comes recipient names
                	$emailTemplateVariables,
                	$storeId
                );
              }
             
    		}catch(Exception $e){
                $this->alr_alert_log($e->getMessage(),'allureAlerts.log');
        	}
	}

/*
	public function sendSalesOfSixEmailAlert($lastOrderdate){
    	try{		
    		$templateId = $this->getConfigHelper()->getSaleEmailTemplate();

    		$emailTemplate = Mage::getModel('core/email_template');
            $storeId = Mage::app()->getStore()->getId();
            $senderName = $this->getConfigHelper()->getAlertSenderName();
            $senderEmail = $this->getConfigHelper()->getAlertSenderEmail();

            $sender = array('name' => $senderName,
                            'email' => $senderEmail);
            
            $recieverEmails = $this->getConfigHelper()->getEmailsGroup();
            $recieverNames = $this->getConfigHelper()->getEmailGroupNames();

            $recipientEmails = explode(',',$recieverEmails);
            $recipientNames = explode(',',$recieverNames);
           
            $emailTemplateVariables['store_name'] = Mage::app()->getStore()->getName();
        	$emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            $emailTemplateVariables['hour_alert'] = 6;
            $emailTemplateVariables['last_order_date'] = $lastOrderdate;
        	
    		if ($templateId) {
                $emailTemplate->sendTransactional(
                	$templateId,
                	$sender,
                	$recipientEmails, //here comes recipient emails
                	$recipientNames, // here comes recipient names
                	$emailTemplateVariables,
                	$storeId
                );
              }
             
    		}catch(Exception $e){
        		$this->alr_alert_log($e->getMessage(),'allureAlerts.log');
        	}
	}

*/
    public function sendCheckoutIssueAlert($collection){
        try{        
            $templateId = $this->getConfigHelper()
            ->getCheckoutIssueEmailTemplate();

            $emailTemplate = Mage::getModel('core/email_template');
            $storeId = Mage::app()->getStore()->getId();
            $senderName = $this->getConfigHelper()->getAlertSenderName();
            $senderEmail = $this->getConfigHelper()->getAlertSenderEmail();

            $sender = array('name' => $senderName,
                            'email' => $senderEmail);
            $recieverEmails = $this->getConfigHelper()->getEmailsGroup();
            $recieverNames = $this->getConfigHelper()->getEmailGroupNames();

            $recipientEmails = explode(',',$recieverEmails);
            $recipientNames = explode(',',$recieverNames);
           
            //$emailTemplateVariables['collection'] = $collection;
            $emailTemplateVariables['store_name'] = Mage::app()->getStore()->getName();
            $emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

            $header = array("customer_email"=>"Customer_Email",
                            "type"=>"Type",
                            "error_message"=>"Error_Message"
                            );

             $folderPath   = Mage::getBaseDir("var") . DS . "alerts" . DS . "checkout_issues";
             $date = date('Y-m-d');
             $filename     = "CHECKOUT_ISSUE_".$date.".csv";            
             $filepath     = $folderPath . DS . $filename;

             $io = new Varien_Io_File();
             $io->setAllowCreateFolders(true);
             $io->open(array("path" => $folderPath));
             $csv = new Varien_File_Csv();

             $rowData = array();
             $rowData[] = $header;
             foreach ($collection as $issue) {
                $row = array();
                $row["customer_email"] = $issue->getCustomerEmail();
                $row["type"] = $issue->getType();
                $row["error_message"] = $issue->getErrorMessage();
                $rowData[] = $row;
             }            

            $csv->saveData($filepath,$rowData);

            if ($templateId) {
                $emailTemplate->getMail()->createAttachment(
                    file_get_contents($filepath),
                    Zend_Mime::TYPE_OCTETSTREAM,
                    Zend_Mime::DISPOSITION_ATTACHMENT,
                    Zend_Mime::ENCODING_BASE64,
                    $filename
                );

                $emailTemplate->sendTransactional(
                    $templateId,
                    $sender,
                    $recipientEmails, //here comes recipient emails
                    $recipientNames, // here comes recipient names
                    $emailTemplateVariables,
                    $storeId
                );
              }
             
            }catch(Exception $e){
                $this->alr_alert_log($e->getMessage(),'allureAlerts.log');
            }
    }

    public function saveAlertIssues($dataIssue){
        try{
            $this->alr_alert_log('in saveAlertIssues','allureAlerts.log');
            Mage::getModel('alertservices/issues')->setData($dataIssue)->save();
        }catch(Exception $e){
            $this->alr_alert_log($e->getMessage(),'allureAlerts.log');
        }
    }
    
    public function sendEmailAlertForNullUsers(){
        try{        
            $templateId = $this->getConfigHelper()->getNullUsersEmailTemplate();

            $emailTemplate = Mage::getModel('core/email_template');
            $storeId = Mage::app()->getStore()->getId();
            $senderName = $this->getConfigHelper()->getAlertSenderName();
            $senderEmail = $this->getConfigHelper()->getAlertSenderEmail();

            $sender = array('name' => $senderName,
                            'email' => $senderEmail);
            $recieverEmails = $this->getConfigHelper()->getEmailsGroup();
            $recieverNames = $this->getConfigHelper()->getEmailGroupNames();

            $recipientEmails = explode(',',$recieverEmails);
            $recipientNames = explode(',',$recieverNames);
           
            $emailTemplateVariables['store_name'] = Mage::app()->getStore()->getName();
            $emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            
            if ($templateId) {
                $emailTemplate->sendTransactional(
                    $templateId,
                    $sender,
                    $recipientEmails, //here comes recipient emails
                    $recipientNames, // here comes recipient names
                    $emailTemplateVariables,
                    $storeId
                );
              }
             
        }catch(Exception $e){
            $this->alr_alert_log($e->getMessage(),'allureAlerts.log');
        }
    }

    public function sendEmailAlertForPageNotFound($collection){
        try{        
            $templateId = $this->getConfigHelper()->getPageNotFoundEmailTemplate();

            $emailTemplate = Mage::getModel('core/email_template');
            $storeId = Mage::app()->getStore()->getId();
            $senderName = $this->getConfigHelper()->getAlertSenderName();
            $senderEmail = $this->getConfigHelper()->getAlertSenderEmail();

            $sender = array('name' => $senderName,
                            'email' => $senderEmail);
            $recieverEmails = $this->getConfigHelper()->getEmailsGroup();
            $recieverNames = $this->getConfigHelper()->getEmailGroupNames();

            $recipientEmails = explode(',',$recieverEmails);
            $recipientNames = explode(',',$recieverNames);
           
            //$emailTemplateVariables['collection'] = $collection;
            $emailTemplateVariables['store_name'] = Mage::app()->getStore()->getName();
            $emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

            $header = array("page_path"=>"Page_Path",
                            "source"=>"Source",
                            "result"=>"Result"
                            );

             $folderPath   = Mage::getBaseDir("var") . DS . "alerts" . DS . "page_not_found";
             $date = date('Y-m-d');
             $filename     = "PAGE_NOT_FOUND_".$date.".csv";            
             $filepath     = $folderPath . DS . $filename;

             $io = new Varien_Io_File();
             $io->setAllowCreateFolders(true);
             $io->open(array("path" => $folderPath));
             $csv = new Varien_File_Csv();

             $rowData = array();
             $rowData[] = $header;
             foreach ($collection as $page) {
                $row = array();
                $row["page_path"] = $page[0];
                $row["source"] = $page[1];
                $row["result"] = '404 Page Not Found';
                $rowData[] = $row;
             } 
            $csv->saveData($filepath,$rowData);

            if ($templateId) {
                $emailTemplate->getMail()->createAttachment(
                    file_get_contents($filepath),
                    Zend_Mime::TYPE_OCTETSTREAM,
                    Zend_Mime::DISPOSITION_ATTACHMENT,
                    Zend_Mime::ENCODING_BASE64,
                    $filename
                );

                $emailTemplate->sendTransactional(
                    $templateId,
                    $sender,
                    $recipientEmails, //here comes recipient emails
                    $recipientNames, // here comes recipient names
                    $emailTemplateVariables,
                    $storeId
                );
              }
             
            }catch(Exception $e){
                $this->alr_alert_log($e->getMessage(),'allureAlerts.log');
            }
    }

    public function sendEmailAlertForAvgPageLoad($totAvgTime){
        try{        
            $templateId = $this->getConfigHelper()->getPageLoadEmailTemplate();

            $emailTemplate = Mage::getModel('core/email_template');
            $storeId = Mage::app()->getStore()->getId();
            $senderName = $this->getConfigHelper()->getAlertSenderName();
            $senderEmail = $this->getConfigHelper()->getAlertSenderEmail();

            $sender = array('name' => $senderName,
                            'email' => $senderEmail);

            $recieverEmails = $this->getConfigHelper()->getEmailsGroup();
            $recieverNames = $this->getConfigHelper()->getEmailGroupNames();

            $recipientEmails = explode(',',$recieverEmails);
            $recipientNames = explode(',',$recieverNames);
           
            //$emailTemplateVariables['collection'] = $collection;
            $emailTemplateVariables['store_name'] = Mage::app()->getStore()->getName();
            $emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            $emailTemplateVariables['avg_load_time'] = $totAvgTime;

            if ($templateId) {
                $emailTemplate->sendTransactional(
                $templateId,
                $sender,
                $recipientEmails, //here comes recipient emails
                $recipientNames, // here comes recipient names
                $emailTemplateVariables,
                $storeId
                );
              }
             
            }catch(Exception $e){
                $this->alr_alert_log($e->getMessage(),'allureAlerts.log');
            }
    }
    
    public function alr_alert_log($message,$filename){
       if (!$this->getConfigHelper()->getAlertDebugStatus()) {
        return;
       }
        Mage::log($message,Zend_log::DEBUG,$filename,true);
      
    }
    public function instaTokenCheck()
    {
        $user_id = Mage::getStoreConfig('allure_instacatalog/feed/user_id');
        $access_token = Mage::getStoreConfig('allure_instacatalog/feed/access_token');
        $limit = 1;

        $instagram = new Instagramclient('');
        $instagram->setAccessToken($access_token);
        $response = $instagram->getUserMedia($user_id,$limit);
        $responseJSON =json_encode($response,true);
        $responseArr = json_decode($responseJSON,true);

        if(isset($responseArr['meta'])){
            if(isset($responseArr['meta']['error_message'])){
                $result = array('message'=>'error',
                    'type'=>$responseArr['meta']['error_type'],
                    'error_message'=>$responseArr['meta']['error_message']);
                return $result;
            }
            else{
               return array("message"=>'done');
            }
        }
    }
    public function sendInstagramErrorEmail($response){
        try{
            $templateId = $this->getConfigHelper()->getInstagramTokenEmailTemplate();
            $storeId = Mage::app()->getStore()->getId();
            $emailTemplate = Mage::getModel('core/email_template');
            $senderName = $this->getConfigHelper()->getAlertSenderName();
            $senderEmail = $this->getConfigHelper()->getAlertSenderEmail();

            $sender = array('name' => $senderName,
                'email' => $senderEmail);

                $recieverEmails = $this->getConfigHelper()->getInstaEmailsGroup();
                $recieverNames = $this->getConfigHelper()->getInstaEmailGroupNames();

            $recipientEmails = explode(',',$recieverEmails);
            $recipientNames = explode(',',$recieverNames);

            $emailTemplateVariables['type'] = $response['type'];
            $emailTemplateVariables['error_message'] = $response['error_message'];

            if ($templateId) {
                $emailTemplate->sendTransactional(
                    $templateId,
                    $sender,
                    $recipientEmails, //here comes recipient emails
                    $recipientNames, // here comes recipient names
                    $emailTemplateVariables,
                    $storeId
                );
            }

        }catch(Exception $e){
            $this->alr_alert_log($e->getMessage(),'allureAlerts.log');
        }
    }

}