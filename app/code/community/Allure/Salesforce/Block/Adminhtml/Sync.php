<?php 
class Allure_Salesforce_Block_Adminhtml_Sync extends Mage_Adminhtml_Block_Widget
{  
    public function __construct(){
        // $this->setTemplate('allure/salesforce/sync.phtml');
    }
    public function getSyncFormUrl(){
        return $this->getUrl('*/*/salesforceSync');
    }
}