<?php

class Allure_Appointments_Block_Adminhtml_Pricing_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct ()
    {
        parent::__construct();
        $this->_objectId = "price_id";
        $this->_blockGroup = "appointments";
        $this->_controller = "adminhtml_appointments";
        $this->_updateButton("save", "label", Mage::helper("appointments")->__("Save Item"));
        $this->_updateButton("delete", "label", Mage::helper("appointments")->__("Delete Item"));
        
        $this->_addButton("saveandcontinue", 
                array(
                        "label" => Mage::helper("appointments")->__("Save And Continue Edit"),
                        "onclick" => "saveAndContinueEdit()",
                        "class" => "save"
                ), - 100);
        
        $this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
						";
    }

    public function getHeaderText ()
    {
        if (Mage::registry("pricing_data") && Mage::registry("pricing_data")->getId()) {
            
            return Mage::helper("appointments")->__("Edit Item '%s'", 
                    $this->htmlEscape(Mage::registry("pricing_data")->getId()));
        } else {
            
            return Mage::helper("appointments")->__("Add Item");
        }
    }
}