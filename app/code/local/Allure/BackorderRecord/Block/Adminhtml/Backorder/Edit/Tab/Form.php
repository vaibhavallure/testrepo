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
                'back' => Mage::helper('backorderrecord')->__('Backordered Items Only'),
                'all' => Mage::helper('backorderrecord')->__('All Items'),
            ),
            'note'      => Mage::helper('backorderrecord')->__('Choose Between Back orders And All Orders'),

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

         $fieldset->addField('sku', 'text', array(
                'name'      => 'item_sku',
                'label'     => Mage::helper("backorderrecord")->__('SKU')
            ));

          $fieldset->addField('show_metal_color', 'select', array(
                'name'      => 'show_metal_color',
                'label'     => Mage::helper("backorderrecord")->__('Metal color'),
                'options'   => array(
                        '0' => Mage::helper('backorderrecord')->__('Any'),
                        '1' => Mage::helper('backorderrecord')->__('Specified'),
                    ),
                'note'      => Mage::helper('backorderrecord')->__('Applies to Any of the Specified Metal Color'),
                'onchange'  => 'showColorHideField()',
            ));

          $selectField = $fieldset->addField('metal_color', 'multiselect', array(
                'name'      => 'metal_color',
                'values'    => $colorValues,
                'display'   => 'none'
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

        $fieldset->addField('show_group', 'select', array(
            'name'      => 'show_group',
            'label'     => Mage::helper("backorderrecord")->__('Group'),
            'options'   => array(
                '0' => Mage::helper('backorderrecord')->__('Any'),
                '1' => Mage::helper('backorderrecord')->__('Specified'),
            ),
            'note'      => Mage::helper('backorderrecord')->__('Applies to Any of the Specified Group'),
            'onchange'  => 'showCustomerGroup()',
        ));

        $selectField = $fieldset->addField('customer_group', 'multiselect', array(
            'name'      => 'customer_group',
            'values'    => $customerGroups,
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
                   
                    $('metal_color').hide();
                        function showColorHideField() {
                            var val1 = document.getElementById('show_metal_color');
                            var hidevalue1 = val1.options[val1.selectedIndex].value;
                            
                           if(hidevalue1 == 1){
                                $('metal_color').show();
                            }else{
                                $('metal_color').hide();
                            } 
                        }
                        
                     $('customer_group').hide();
                        function showCustomerGroup() {
                            var val2 = document.getElementById('show_group');
                            var hidevalue2 = val2.options[val2.selectedIndex].value;
                            
                           if(hidevalue2 == 1){
                                $('customer_group').show();
                            }else{
                                $('customer_group').hide();
                            } 
                        }

                </script>");
       
        $form->setValues(null);

        return parent::_prepareForm();
    }
}