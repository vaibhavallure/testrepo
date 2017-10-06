<?php
class Allure_Reports_Block_Adminhtml_Report_Filter_Form extends Mage_Adminhtml_Block_Report_Filter_Form
{
    /**
     * Add fields to base fieldset which are general to sales reports
     *
     * @return Mage_Sales_Block_Adminhtml_Report_Filter_Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

        if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset) {

            $statuses = Mage::getModel('sales/order_config')->getStatuses();
            $values = array();
            foreach ($statuses as $code => $label) {
                    $values[] = array(
                        'label' => Mage::helper('reports')->__($label),
                        'value' => $code
                    );
            }

            $fieldset->addField('show_order_statuses', 'select', array(
                'name'      => 'show_order_statuses',
                'label'     => Mage::helper('reports')->__('Order Status'),
                'options'   => array(
                        '0' => Mage::helper('reports')->__('Any'),
                        '1' => Mage::helper('reports')->__('Specified'),
                    ),
                'note'      => Mage::helper('reports')->__('Applies to Any of the Specified Order Statuses'),
            ), 'to');

            $fieldset->addField('order_statuses', 'multiselect', array(
                'name'      => 'order_statuses',
                'values'    => $values,
                'display'   => 'none'
            ), 'show_order_statuses');
            
            $data = $this->getFilterData()->getData();
            $store_ids = $data['store_ids'];
            $isCounterpointStore = false;
            $storeCode = "";
            if(!empty($store_ids)){
                $storeIds = explode(",", $store_ids);
                $storeId = $storeIds[0];
                $storeObj = Mage::getModel('core/store')->load($storeId);
                if($storeObj->getCode() == "counterpoint_vba" || $storeObj->getCode() == "counterpoint_vmt"){
                    $isCounterpointStore = true;
                    $storeCode = $storeObj->getCode();
                }
            }
            
            if($isCounterpointStore){
                $stationValues = $this->getCounterpointStationValues($storeCode);
                $fieldset->addField('show_counterpoint_sta_id', 'select', array(
                    'name'      => 'show_counterpoint_sta_id',
                    'label'     => Mage::helper('reports')->__('Counterpoint STA Id'),
                    'options'   => array(
                        '0' => Mage::helper('reports')->__('Any'),
                        '1' => Mage::helper('reports')->__('Specified'),
                    ),
                    'note'      => Mage::helper('reports')->__('Applies to Any of the Specified Order Statuses'),
                ), 'order_statuses');
                
                $fieldset->addField('counterpoint_sta_id', 'multiselect', array(
                    'name'      => 'counterpoint_sta_id',
                    'values'    => $stationValues,
                    'display'   => 'none'
                ), 'show_counterpoint_sta_id');
            }

            // define field dependencies
            if ($this->getFieldVisibility('show_order_statuses') && $this->getFieldVisibility('order_statuses')) {
                if($isCounterpointStore){
                    $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                        ->addFieldMap("{$htmlIdPrefix}show_order_statuses", 'show_order_statuses')
                        ->addFieldMap("{$htmlIdPrefix}order_statuses", 'order_statuses')
                        ->addFieldDependence('order_statuses', 'show_order_statuses', '1')
                        ->addFieldMap("{$htmlIdPrefix}show_counterpoint_sta_id", 'show_counterpoint_sta_id')
                        ->addFieldMap("{$htmlIdPrefix}counterpoint_sta_id", 'counterpoint_sta_id')
                        ->addFieldDependence('counterpoint_sta_id', 'show_counterpoint_sta_id', '1')
                        );
                }else{
                    $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                        ->addFieldMap("{$htmlIdPrefix}show_order_statuses", 'show_order_statuses')
                        ->addFieldMap("{$htmlIdPrefix}order_statuses", 'order_statuses')
                        ->addFieldDependence('order_statuses', 'show_order_statuses', '1')
                        );
                }
            }
        }

        return $this;
    }
    
    /**
     * get counterpoint station values
     */
    private function getCounterpointStationValues($storeCode){
        $values = array();
        if(!empty($storeCode)){
            if($storeCode == "counterpoint_vmt"){
                $values[] = array('label'=>'21' ,'value'=>'21');
                $values[] = array('label'=>'22' ,'value'=>'22');
                $values[] = array('label'=>'23' ,'value'=>'23');
                $values[] = array('label'=>'24' ,'value'=>'24');
                $values[] = array('label'=>'29' ,'value'=>'29');
            }else{
                $values[] = array('label'=>'1' ,'value'=>'1');
                $values[] = array('label'=>'2' ,'value'=>'2');
            }
        }
        return $values;
    }
}
