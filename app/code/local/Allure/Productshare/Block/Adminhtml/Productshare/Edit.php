<?php

class Allure_Productshare_Block_Adminhtml_Productshare_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct ()
    {
        parent::__construct();
        $this->_objectId = "ps_id";
        $this->_blockGroup = "productshare";
        $this->_controller = "adminhtml_productshare";
        $this->_updateButton("save", "label", Mage::helper("productshare")->__("Save Item"));
        $this->_updateButton("delete", "label", Mage::helper("productshare")->__("Delete Item"));
        
        $this->_addButton("saveandcontinue", 
                array(
                        "label" => Mage::helper("productshare")->__("Save And Continue Edit"),
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
        if (Mage::registry("productshare_data") && Mage::registry("productshare_data")->getId()) {
            
            return Mage::helper("productshare")->__("Edit Item '%s'", 
                    $this->htmlEscape(Mage::registry("productshare_data")->getId()));
        } else {
            
            return Mage::helper("productshare")->__("Add Item");
        }
    }
}