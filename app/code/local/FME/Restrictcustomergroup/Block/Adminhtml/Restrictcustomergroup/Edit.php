<?php

class FME_Restrictcustomergroup_Block_Adminhtml_Restrictcustomergroup_Edit
	extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'restrictcustomergroup';
        $this->_controller = 'adminhtml_restrictcustomergroup';
        
        $this->_updateButton('save', 'label', Mage::helper('restrictcustomergroup')->__('Save Item Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('restrictcustomergroup')->__('Delete Item Rule'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
		
		$key = $this->getRequest()->getParam('key');/*Mage::getSingleton('adminhtml/url')
             ->getSecretKey("adminhtml_restrictcustomergroup/new","new");*/
			 
        $this->_formScripts[] = "
			var ADMIN_URL = '".Mage::getBaseUrl()."';
			
            function toggleEditor() {
                if (tinyMCE.getInstanceById('description') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'description');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'description');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
			
			function setType(p) {
				//alert(p);return false;
				setLocation(p);
			}
			
			function changeParam(e) {
			
				//alert(e.value);return false;
				var param = e.value;
	
				if (param != 'undefined') {
					var url = ADMIN_URL+'restrictcustomergroup/adminhtml_restrictcustomergroup/new/type/'+param+'/key/".$key."';
					//alert(url);return false;
					setLocation(url);
				}
				//alert(url);return false;
				
			}
        ";
    }

	protected function _prepareLayout() {
		parent::_prepareLayout();
		
		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled())
		{
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		}
    }
	
    public function getHeaderText()
    {
        if( Mage::registry('restrictcustomergroup_data') && Mage::registry('restrictcustomergroup_data')->getId() )
		{
            return Mage::helper('restrictcustomergroup')->__("Edit Rule '%s'", $this->htmlEscape(Mage::registry('restrictcustomergroup_data')->getTitle()));
        }
		else
		{
            return Mage::helper('restrictcustomergroup')->__('New Rule');
        }
    }
}