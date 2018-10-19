<?php 
class Allure_Salesforce_Block_Adminhtml_Gen extends Mage_Adminhtml_Block_Widget
{  
    public function __construct(){
        //$this->setTemplate('allure/salesforce/gen.phtml');
    }
    
    public function getGenerateCsvFormUrl(){
        return $this->getUrl('*/*/generateCsv');
    }
    
    public function getuploadCsvFormUrl(){
        return $this->getUrl('*/*/uploadform');
    }
    
    public function getIndexUrl(){
        return $this->getUrl('*/*/index');
    }
    
}