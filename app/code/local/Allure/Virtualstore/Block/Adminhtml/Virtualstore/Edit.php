<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/14/18
 * Time: 6:11 PM
 */
class Allure_Virtualstore_Block_Adminhtml_Virtualstore_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {

        parent::__construct();
        $this->_objectId = "store_id";
        $this->_blockGroup = "virtualstore";
        $this->_controller = "adminhtml_virtualstore";

        $this->_updateButton('save', 'label', Mage::helper('virtualstore')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('virtualstore')->__('Delete Item'));

        $this->_addButton("saveandcontinue", array(
            "label"     => Mage::helper("virtualstore")->__("Save And Continue Edit"),
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
        if( Mage::registry("virtualstore_data") && Mage::registry("virtualstore_data")->getId() ){

            return Mage::helper("virtualstore")->__("Edit virtualstore '%s'", $this->htmlEscape(Mage::registry("virtualstore_data")->getId()));

        }
        else{

            return Mage::helper("virtualstore")->__("Add Store");

        }
    }
}