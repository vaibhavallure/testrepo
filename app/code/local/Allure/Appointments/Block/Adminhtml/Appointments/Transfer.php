<?php

class Allure_Appointments_Block_Adminhtml_Appointments_Transfer extends Mage_Adminhtml_Block_Widget_Form_Container
{
    
    public function __construct ()
    {
        parent::__construct();
        $this->_objectId = "id";
        $this->_blockGroup = "appointments";
        $this->_controller = "adminhtml_appointments";
        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->_removeButton('delete');
        
        $this->_addButton("saveandcontinue",
            array(
                "label" => Mage::helper("appointments")->__("Transfer"),
                "onclick" => "saveAndContinueEdit()",
                "class" => "save"
            ), - 100);
        
        $this->_formScripts[] = "
            
							function saveAndContinueEdit(){
								editForm.submit('{$this->getConfirmationUrl()}');
							}
						";
        
    }
    
    public function getHeaderText ()
    {
            return Mage::helper("appointments")->__("Transfer Appointments");
    }
    public function getConfirmationUrl()
    {
        $key=Mage::getSingleton('core/session')->getFormKey();
        // $this->_objectId=$this->getRequest()->getParam($this->_objectId);
        return $this->getUrl('admin_appointments/adminhtml_appointments/dotransfer/', array('form_key'=>$key,'_secure' => true));
    }
    
}