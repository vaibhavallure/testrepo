<?php


class Allure_Reports_Block_Adminhtml_Report_Filter_Form_Order extends Allure_Reports_Block_Adminhtml_Report_Filter_Form
{
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

        if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset) {

            $fieldset->addField('show_actual_columns', 'select', array(
                'name'       => 'show_actual_columns',
                'options'    => array(
                    '1' => Mage::helper('reports')->__('Yes'),
                    '0' => Mage::helper('reports')->__('No')
                ),
                'label'      => Mage::helper('reports')->__('Show Actual Values'),
            ));

        }
        
        //allure
        $fieldset->removeField('show_empty_rows');
     //   $fieldset->removeField('show_order_statuses');
        return $this;
    }
    
    
    protected function _initFormValues()
    {
        $data = $this->getFilterData()->getData();
        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value[0])) {
                $data[$key] = explode(',', $value[0]);
            }
        }
        
        //allure change
        $dateKeys = array("from","to");
        foreach ($dateKeys as $key){
            if(array_key_exists($key, $data)){
                $data[$key] = str_replace(",", "", $data[$key]);
            }
        }
        
        $this->getForm()->addValues($data);
    }
}
