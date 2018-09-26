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
            $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
            $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');        
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
        	  Mage::log($e->getMessage(),Zend_log::DEBUG,'allureAlerts.log',true);
        	}
	}

	public function sendSalesOfFourEmailAlert(){
    	try{		
    		$templateId = $this->getConfigHelper()
            ->getSaleEmailTemplate();

    		$emailTemplate = Mage::getModel('core/email_template');
            $storeId = Mage::app()->getStore()->getId();
            $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
            $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');        
            $sender = array('name' => $senderName,
                            'email' => $senderEmail);
            $recieverEmails = $this->getConfigHelper()->getEmailsGroup();
            $recieverNames = $this->getConfigHelper()->getEmailGroupNames();

            $recipientEmails = explode(',',$recieverEmails);
            $recipientNames = explode(',',$recieverNames);
           
            $emailTemplateVariables['store_name'] = Mage::app()->getStore()->getName();
        	$emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            $emailTemplateVariables['hour_alert'] = 4; 
        	
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
        	 Mage::log($e->getMessage(),Zend_log::DEBUG,'allureAlerts.log',true);
        	}
	}


	public function sendSalesOfSixEmailAlert(){
    	try{		
    		$templateId = $this->getConfigHelper()
            ->getSaleEmailTemplate();

    		$emailTemplate = Mage::getModel('core/email_template');
            $storeId = Mage::app()->getStore()->getId();
            $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
            $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');        
            $sender = array('name' => $senderName,
                            'email' => $senderEmail);
            $recieverEmails = $this->getConfigHelper()->getEmailsGroup();
            $recieverNames = $this->getConfigHelper()->getEmailGroupNames();

            $recipientEmails = explode(',',$recieverEmails);
            $recipientNames = explode(',',$recieverNames);
           
            $emailTemplateVariables['store_name'] = Mage::app()->getStore()->getName();
        	$emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            $emailTemplateVariables['hour_alert'] = 6;
        	
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
        		echo $e->getMessage();
        	}
	}


    public function sendCheckoutIssueAlert($collection){
        try{        
            $templateId = $this->getConfigHelper()
            ->getCheckoutIssueEmailTemplate();

            $emailTemplate = Mage::getModel('core/email_template');
            $storeId = Mage::app()->getStore()->getId();
            $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
            $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');        
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
                echo $e->getMessage();
            }
    }

    public function saveAlertIssues($dataIssue){
        try{
            Mage::log('in saveAlertIssues',Zend_log::DEBUG,'allureAlerts.log',true);
            Mage::getModel('alertservices/issues')->setData($dataIssue)->save();
        }catch(Exception $e){
            Mage::log($e->getMessage(),Zend_log::DEBUG,'allureAlerts.log',true);
        }
    }
    
    public function sendEmailAlertForNullUsers(){
        try{        
            $templateId = $this->getConfigHelper()->getNullUsersEmailTemplate();

            $emailTemplate = Mage::getModel('core/email_template');
            $storeId = Mage::app()->getStore()->getId();
            $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
            $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');        
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
                echo $e->getMessage();
        }
    }

    public function sendEmailAlertForPageNotFound($collection){
        try{        
            $templateId = $this->getConfigHelper()->getPageNotFoundEmailTemplate();

            $emailTemplate = Mage::getModel('core/email_template');
            $storeId = Mage::app()->getStore()->getId();
            $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
            $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');        
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
                $row["page_path"] = $page;
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
                echo $e->getMessage();
            }
    }
    
                 

}