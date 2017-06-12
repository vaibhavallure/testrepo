<?php

class FME_Restrictcustomergroup_Block_Adminhtml_Restrictcustomergroup_Edit_Tab_Type
    extends Mage_Adminhtml_Block_Widget_Form {
     
    protected function _prepareLayout() {
        
        $this->setChild('continue_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Continue'),
                    'onclick'   => "setType('".$this->getContinueUrl()."')",
                    'class'     => 'save'
                    )
                )
        );
        return parent::_prepareLayout();
    }
    
    protected function _prepareForm() {
	
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('restrictcustomergroup_form_type',
					array(
						
					   'legend' => Mage::helper('restrictcustomergroup')->__('Form Type')
				    )
				 );
     
      $fieldset->addField('rule_type', 'select', array(
            'label' => Mage::helper('catalog')->__('Type'),
            'title' => Mage::helper('catalog')->__('Type'),
            'name'  => 'set',
            //'value' => $entityType->getDefaultAttributeSetId(),
            'values'=> array(
                array(
                  'label' => 'Select Type',
				  'value' => 0
                ),
                array(
                    'label' => 'Basic',
                    'value' => 'basic'
                ),
                array(
                    'label' => 'Manual',
                    'value' => 'manual'
                ),
            ),
            'onchange' => 'changeParam(this)',
			'after_element_html' => Mage::helper('restrictcustomergroup')->__('Upon choosing form type, will automatically redirect to that form')
            
        ));
      
      //$fieldset->addField('continue_button', 'note', array(
      //    'text' => $this->getChildHtml('continue_button'),
      //));

      $this->setForm($form);
      //return parent::_prepareForm();
    }
    
    public function getContinueUrl() {
        
        return $this->getUrl('*/*/new', array(
            '_current'  => true,
            //'set'       => '{{attribute_set}}',
			'type' => 'manual'
        ));
    }
}