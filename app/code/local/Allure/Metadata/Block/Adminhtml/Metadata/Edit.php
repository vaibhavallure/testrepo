<?php
	
class Allure_Metadata_Block_Adminhtml_Metadata_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "id";
				$this->_blockGroup = "metadata";
				$this->_controller = "adminhtml_metadata";
				$this->_updateButton("save", "label", Mage::helper("metadata")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("metadata")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("metadata")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);



				$this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
						";
		}

		public function getHeaderText()
		{
				if( Mage::registry("metadata_data") && Mage::registry("metadata_data")->getId() ){

				    return Mage::helper("metadata")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("metadata_data")->getId()));

				} 
				else{

				     return Mage::helper("metadata")->__("Add Item");

				}
		}
}