<?php
class Allure_Metadata_Block_Adminhtml_Metadata_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("metadata_form", array("legend"=>Mage::helper("metadata")->__("Meta Information")));

						$fieldset->addField("term", "text", array(
						"label" => Mage::helper("metadata")->__("Term"),
						"name" => "term",
						));
					
						$fieldset->addField("title", "text", array(
						"label" => Mage::helper("metadata")->__("Title"),
						"name" => "title",
						));
					
						$fieldset->addField("description", "textarea", array(
						    "label" => Mage::helper("metadata")->__("Description"),
						    "name" => "description",
						));
						$fieldset->addField("status", "select", array(
						    "label" => Mage::helper("metadata")->__("Status"),
						    "name" => "status",
						    'options'     => array(
						        1 => 'Enabled',
						        0 => 'Disabled',
						       
						    ),
						));
						
						if (Mage::getSingleton("adminhtml/session")->getMetadataData())
				{
				    $form->setValues(Mage::getSingleton("adminhtml/session")->getMetadataData());
				    Mage::getSingleton("adminhtml/session")->setMetadataData(null);
				} 
				elseif(Mage::registry("metadata_data")) {
				    $form->setValues(Mage::registry("metadata_data")->getData());
				}
				return parent::_prepareForm();
		}
}
