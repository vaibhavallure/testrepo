<?php

class Allure_Salesforce_Adminhtml_GenController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction(){
        $params = $this->getRequest()->getParams();
        $uid = $params["uid"];
        if(!$uid){
            $uid = Mage::getSingleton("adminhtml/session")->getUid();
        }
        if($uid != "allure"){
            Mage::getSingleton("adminhtml/session")->setUid($params["uid"]);
            $this->loadLayout();
            $this->_title($this->__("Salesforce CSV Generate"));
            $this->_addContent( $this->getLayout()
                ->createBlock('allure_salesforce/adminhtml_gen')
                ->setTemplate("allure/salesforce/noaccess.phtml")
               );
            $this->renderLayout();
        }else{
            $this->loadLayout();
            $this->_title($this->__("Salesforce CSV Generate"));
            $this->_addContent( $this->getLayout()
               ->createBlock('allure_salesforce/adminhtml_gen')
                ->setTemplate("allure/salesforce/gen.phtml")
               );
            $this->renderLayout();
        }
    }
    
    public function generateCsvAction(){
        $formData = $this->getRequest()->getParams();
        $pageNum  = $formData["page"];
        $size     = $formData["size"];
        $fields   = $formData["fields"];
        $objectType = $formData["object_type"];
        $subFields = explode(",", $fields);
        $header      = array();
        $tableHeader = array();
        $isError = false;
        $message = "";
        
        $_session = Mage::getSingleton("adminhtml/session");
        
        if(!$objectType){
            $_session->addError(
                Mage::helper("allure_salesforce")->__("Invalid object type.")
            );
            $this->_redirect("*/*/");
        }
        
        if(!is_numeric($pageNum) || !is_numeric($size)){
            $_session->addError(
                Mage::helper("allure_salesforce")->__("Page number or page size must in number format.i.e[0-9]")
            );
            $this->_redirect("*/*/");
        }
        
        foreach ($subFields as $subF){
            $cols = explode("=" , $subF);
            if(count($cols) != 2){
                $isError = true;
                $message = "Key-Value pair of field is wrong.Field is \"$subF\"";
                break;
            }
            $col1 = trim($cols[0]);
            $col2 = trim($cols[1]);
            $header[$col1] = $col1;
            $tableHeader[$col2] = $col1;
        }
        
        try{
            $helper = Mage::helper("allure_salesforce/csv");
            $result = $helper->generateCsv($objectType, $pageNum, $size, $header, $tableHeader);
            if($result["success"]){
                $filename = $result["filename"];
                $filePath = $result["path"];
            }else{
                $isError = true;
                $message = $result["message"];
            }
            
        }catch (Exception $e){
            $isError = true;
            $message = $e->getMessage();
        }
        
        if($isError){
            $_session->addError(
                Mage::helper("allure_salesforce")->__($message)
            );
        }else{
            $content = array("type" => "filename", "value" => $filePath);
            $this->_prepareDownloadResponse($filename, $content);
            /* $_session->addSuccess(
                Mage::helper("allure_salesforce")->__("\"$filename\" file downloaded.")
            ); */
        }
        $this->_redirect("*/*/");
    }
    
    public function uploadformAction(){
        $maxUploadSize = Mage::helper("importexport")->getMaxUploadSize();
        $this->_getSession()->addNotice(
            $this->__('Total size of uploadable files must not exceed %s', $maxUploadSize)
        );
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function uploadsaveAction(){
        $isError = false;
        $_fileName = "";
        $message   = "";
        if($data = $this->getRequest()->getPost()){
            $objectType = $data["object_type"];
            $_fileName = $_FILES["import_file"]["name"];
            if(isset($_fileName) && $_fileName != ""){
                try{
                    $csvHelper = Mage::helper("allure_salesforce/csv");
                    $response = $csvHelper->uploadCsvFile($_fileName);
                    if($response["success"]){
                        $_filePath = $response["file_path"];
                        $result  = $csvHelper->parseCsvFile($_filePath, $objectType);
                        if($result["success"]){
                            $message = $result["message"];
                            $failure = $result["failure"];
                            $failMessage = $result["fail_message"];
                        }else{
                            $isError = true;
                            $message = $result["message"];
                        }
                    }else {
                        $isError = true;
                        $message = $response["message"];
                    }
                }catch (Exception $e){
                    $isError = true;
                    $message = $e->getMessage();
                }
            }
        }
        
        if($isError){
           Mage::getSingleton("adminhtml/session")->addError(
               Mage::helper("allure_salesforce")->__($_fileName ." ". $message)
           ); 
        }else {
            if($failure){
                Mage::getSingleton("adminhtml/session")->addError(
                    Mage::helper("allure_salesforce")->__($failMessage)
                );
            }
            Mage::getSingleton("adminhtml/session")->addSuccess(
                Mage::helper("allure_salesforce")->__($message)
            );
        }
        $this->_redirect("*/*/uploadform");
    }
}
