<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/20/18
 * Time: 3:54 PM
 */
class Allure_Virtualstore_Block_Adminhtml_Website_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {

        parent::__construct();
        $this->_objectId = "website_id";
        $this->_blockGroup = "allure_virtualstore";
        $this->_controller = "adminhtml_website";

        $this->_updateButton('save', 'label', Mage::helper('allure_virtualstore')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('allure_virtualstore')->__('Delete Item'));

        $this->_addButton("saveandcontinue", array(
            "label"     => Mage::helper("allure_virtualstore")->__("Save And Continue Edit"),
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
        if( Mage::registry("website_data") && Mage::registry("website_data")->getWebsiteId() ){

            return Mage::helper("allure_virtualstore")->__("Edit website '%s'", $this->htmlEscape(Mage::registry("website_data")->getWebsiteId()));

        }
        else{

            return Mage::helper("allure_virtualstore")->__("Add Website");

        }
    }
}