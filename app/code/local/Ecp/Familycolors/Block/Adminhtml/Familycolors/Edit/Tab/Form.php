<?php
class Ecp_Familycolors_Block_Adminhtml_Familycolors_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('familycolors_form', array('legend'=>Mage::helper('familycolors')->__('Edit Style')));

        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
        	array(
        		'add_variables' => false,
        		'add_widgets' => false,
        		'files_browser_window_url'=>$this->getUrl().'admin/cms_wysiwyg_images/index/'
        	)
        );
       
        
        $fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('familycolors')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
        ));
        
       
    /*  $fieldset->addField('image', 'image', array(
            'label'     => Mage::helper('patterns')->__('Image'),
            'required'  => true,
             'name'      => 'image',
       ));*/
        
      
        $fieldset->addField('description', 'text', array(
            'name'      => 'description',
            'label'     => Mage::helper('familycolors')->__('Description'),
            'title'     => Mage::helper('familycolors')->__('Description'),
            'style'     => 'width:98%; height:200px;',
            'wysiwyg'   => true,
            'required'  => true,
            'config'    => $wysiwygConfig
        ));

       

        $apparelcolors = Mage::helper('familycolors')->getMultiAttributeValues('color_apparel');
        $diamondcolors =	Mage::helper('familycolors')->getMultiAttributeValues('diamond_color');
		$metalcolors = Mage::helper('familycolors')->getMultiAttributeValues('metal_color');
        $fieldset->addField('color_apparel', 'multiselect', array(
           'name'     => 'color_apparel',
           'label'    => Mage::helper('familycolors')->__('Apparel Colors'),
           'title'    => Mage::helper('familycolors')->__('Apparel Colors'),
           'style'    => 'width:98%; height:400px;',
           'values'   => $apparelcolors,
        ));
	/*
        $fieldset->addField('diamond_color', 'multiselect', array(
           'name'     => 'diamond_color',
           'label'    => Mage::helper('familycolors')->__('Diamond Colors'),
           'title'    => Mage::helper('familycolors')->__('Diamond Colors'),
           'style'    => 'width:98%; height:400px;',
           'values'   => $diamondcolors,
        ));
		        $fieldset->addField('metal_color', 'multiselect', array(
           'name'     => 'metal_color',
           'label'    => Mage::helper('familycolors')->__('Metal Colors'),
           'title'    => Mage::helper('familycolors')->__('Metal Colors'),
           'style'    => 'width:98%; height:400px;',
           'values'   => $metalcolors,
        ));
         * */
         
        if ( Mage::getSingleton('adminhtml/session')->getFamilycolorsData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFamilycolorsData());
            Mage::getSingleton('adminhtml/session')->setFamilycolorsData(null);
        } elseif ( Mage::registry('familycolors_data') ) {
            $form->setValues(Mage::registry('familycolors_data')->getData());
        }
        $this->updateFormValues($form);
        return parent::_prepareForm();
    }
    protected function updateFormValues($form)
    {
        if ( Mage::getSingleton('adminhtml/session')->getFamilycolorsData() )
        {
            $data = Mage::getSingleton('adminhtml/session')->getFamilycolorsData();
            if (isset($data['color'])){
                if (is_string($data['color'])){
                    $data['color'] = unserialize($data['color']);
                }
            }

            $form->setValues($data);
            Mage::getSingleton('adminhtml/session')->setFamilycolorsData(null);

        } elseif ( Mage::registry('familycolors_data') ) {

            $data = Mage::registry('familycolors_data')->getData();
            if (isset($data['color_apparel'])&&$data['color_apparel']!='Array') {
                if (is_string($data['color_apparel'])){
                    try{
                        $data['color_apparel'] = unserialize($data['color_apparel']);
                    }catch(Exception $e){}
                }
            }
            if(isset($data['diamond_color'])&&$data['diamond_color']!='Array') {
                if (is_string($data['diamond_color'])){
                    try{
                        $data['diamond_color'] = unserialize($data['diamond_color']);
                    }catch(Exception $e){}
                }
            }
            if(isset($data['metal_color'])&&$data['metal_color']!='Array') {
                if (is_string($data['metal_color'])){
                    try{
                    $data['metal_color'] = unserialize($data['metal_color']);
                    }catch(Exception $e){}
                }
            }
            $form->setValues($data);
        }
    }
}
