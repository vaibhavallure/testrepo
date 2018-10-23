<?php

class Allure_BackorderRecord_Block_Adminhtml_Backorder_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();
        $this->setForm($form);


        $fieldset = $form->addFieldset('backorederrecord_form', array(
            'legend'=>Mage::helper('backorderrecord')->__('Select Date To Download back order')
        ));

        $dateTimeFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $statuses = Mage::getModel('sales/order_config')->getStatuses();
        $values = array();
            foreach ($statuses as $code => $label) {
                    $values[] = array(
                        'label' => Mage::helper("backorderrecord")->__($label),
                        'value' => $code
                    );
            }


        $fieldset->addField('fromdate', 'datetime', array(
            'label'    => 'From',
            'title'    => 'From',
            'time'      => true,
            'name'     => 'from_date',
            'image'    => $this->getSkinUrl('images/grid-cal.gif'),
            'format'   => $dateTimeFormatIso,
            'required' => true,
        ));

        $fieldset->addField('todate', 'datetime', array(
            'label'    => 'To',
            'title'    => 'To',
            'time'      => true,
            'name'     => 'to_date',
            'image'    => $this->getSkinUrl('images/grid-cal.gif'),
            'format'   => $dateTimeFormatIso,
            'required' => true,
        ));

         $fieldset->addField('sku', 'text', array(
                'name'      => 'item_sku',
                'label'     => Mage::helper("backorderrecord")->__('SKU')
            ));

          $fieldset->addField('metal_color', 'text', array(
                'name'      => 'metal_color',
                'label'     => Mage::helper("backorderrecord")->__('Metal color')
            ));


       $fieldset->addField('show_order_statuses', 'select', array(
                'name'      => 'show_order_statuses',
                'label'     => Mage::helper('reports')->__('Order Status'),
                'options'   => array(
                        '0' => Mage::helper('reports')->__('Any'),
                        '1' => Mage::helper('reports')->__('Specified'),
                    ),
                'note'      => Mage::helper('reports')->__('Applies to Any of the Specified Order Statuses'),
                'onchange'  => 'showHideField()',
            ));
        

        $selectField = $fieldset->addField('order_statuses', 'multiselect', array(
                'name'      => 'order_statuses',
                'values'    => $values,
                'display'   => 'none'
            ));

        $selectField->setAfterElementHtml("
                <script type=\"text/javascript\">
                    $('order_statuses').hide();
                        function showHideField() {
                            var val = document.getElementById('show_order_statuses');
                            var hidevalue = val.options[val.selectedIndex].value;
                            
                           if(hidevalue == 1){
                            $('order_statuses').show();
                            }else{
                                $('order_statuses').hide();
                            } 
                        }
                </script>"); 
       
        $form->setValues(null);

        return parent::_prepareForm();
    }
}