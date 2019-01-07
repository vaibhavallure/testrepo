<?php

class Allure_BackorderRecord_Block_Adminhtml_Refund_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();
        $this->setForm($form);


        $fieldset = $form->addFieldset('Refund_form', array(
            'legend'=>Mage::helper('backorderrecord')->__('Select Date To Download Refund Report')
        ));

        $dateTimeFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $statuses = Mage::getModel('sales/order_config')->getStatuses();
        $values = array();

        $metalColor = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'metal');

        if ($metalColor->usesSource()) {
            $colorOptions = $metalColor->getSource()->getAllOptions(false);
        }

        $c_groups = Mage::getModel('customer/group')->getCollection();

        $colorValues = array();
        $customerGroups = array();

        foreach($c_groups as $c_type) {

//            if ($c_type->getCustomerGroupId() != 0){
                $customerGroups[] = array (
                    'label' => Mage::helper("backorderrecord")->__($c_type->getCustomerGroupCode()),
                    'value' => $c_type->getCustomerGroupId()
                );
//            }
        }

        foreach ($statuses as $code => $label) {
                    $values[] = array(
                        'label' => Mage::helper("backorderrecord")->__($label),
                        'value' => $code
                    );
            }

            foreach ($colorOptions as $option) {
                    $colorValues[] = array(
                        'label' => Mage::helper("backorderrecord")->__($option['label']),
                        'value' => $option['label']
                    );
            }
            sort($colorValues);
            sort($values);
            sort($customerGroups);



        $fieldset->addField('order_type', 'select', array(
            'name'      => 'order_type',
            'label'     => Mage::helper("backorderrecord")->__('Order Type'),
            'options'   => array(
                'orderdate' => Mage::helper('backorderrecord')->__('Report By Order Date'),
                'refunddate' => Mage::helper('backorderrecord')->__('Report By Refund date'),
            ),

        ));

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




       $fieldset->addField('show_order_statuses', 'select', array(
                'name'      => 'show_order_statuses',
                'label'     => Mage::helper('backorderrecord')->__('Order Status'),
                'options'   => array(
                        '0' => Mage::helper('backorderrecord')->__('Any'),
                        '1' => Mage::helper('backorderrecord')->__('Specified'),
                    ),
                'note'      => Mage::helper('backorderrecord')->__('Applies to Any of the Specified Order Statuses'),
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