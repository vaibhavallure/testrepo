<?php

class Ecp_Footlinks_Block_Adminhtml_Footlinks_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'footlinks';
        $this->_controller = 'adminhtml_footlinks';
        
        $this->_updateButton('save', 'label', Mage::helper('footlinks')->__('Save Link'));
        $this->_updateButton('delete', 'label', Mage::helper('footlinks')->__('Delete Link'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            
            window.onload=function(){
                var selected = edit_form.type.options[edit_form.type.selectedIndex].value;
                
                var checkboxSelected = edit_form.use_for_seo_text.checked;
                if(checkboxSelected){
                    edit_form.block_for_seo_default.up('td').up('tr').show();
                    edit_form.block_for_home_seo.up('td').up('tr').show();
                    edit_form.block_value.up('td').up('tr').hide();
                    edit_form.type.up('td').up('tr').hide();
                    edit_form.url_value.up('td').up('tr').hide();
                }else{
                    edit_form.block_for_seo_default.up('td').up('tr').hide();
                    edit_form.block_for_home_seo.up('td').up('tr').hide();
                    if( selected == 1){
                        edit_form.block_value.up('td').up('tr').show();
                        edit_form.url_value.up('td').up('tr').hide();
                    }else{
                        edit_form.block_value.up('td').up('tr').hide();
                        edit_form.url_value.up('td').up('tr').show();
                    }
                }
                
                
            }            
            
            function toggleEditor() {
                if (tinyMCE.getInstanceById('footlinks_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'footlinks_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'footlinks_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
            function hideSelected(){
                var selected = edit_form.type.options[edit_form.type.selectedIndex].value;

                if( selected == 1){
                    edit_form.block_value.up('td').up('tr').show();
                    edit_form.url_value.up('td').up('tr').hide();
                }else{
                    edit_form.block_value.up('td').up('tr').hide();
                    edit_form.url_value.up('td').up('tr').show();
                }
            }
            
            function changeForSeo(checkbox){
                if($(checkbox).checked){
                    edit_form.block_for_seo_default.up('td').up('tr').show();
                    edit_form.block_for_home_seo.up('td').up('tr').show();
                    edit_form.block_value.up('td').up('tr').hide();
                    edit_form.url_value.up('td').up('tr').hide();
                    edit_form.type.up('td').up('tr').hide();
                }else{
                    edit_form.block_for_seo_default.up('td').up('tr').hide();
                    edit_form.block_for_home_seo.up('td').up('tr').hide();
                    edit_form.block_value.up('td').up('tr').show();
                    edit_form.url_value.up('td').up('tr').show();
                    edit_form.type.up('td').up('tr').show();
                    
                    var selected = edit_form.type.options[edit_form.type.selectedIndex].value;
                    if( selected == 1){
                        edit_form.block_value.up('td').up('tr').show();
                        edit_form.url_value.up('td').up('tr').hide();
                    }else{
                        edit_form.block_value.up('td').up('tr').hide();
                        edit_form.url_value.up('td').up('tr').show();
                    }
                }
                
                
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('footlinks_data') && Mage::registry('footlinks_data')->getId() ) {
            return Mage::helper('footlinks')->__("Edit Link '%s'", $this->htmlEscape(Mage::registry('footlinks_data')->getTitle()));
        } else {
            return Mage::helper('footlinks')->__('Add Link');
        }
    }
}