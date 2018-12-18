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
            
            $fieldset->addField('show_order_paymentmethod', 'select', array(
                'name'      => 'show_order_paymentmethod',
                'label'     => Mage::helper('reports')->__('Order Payment Method'),
                'options'   => array(
                    '0' => Mage::helper('reports')->__('Any'),
                    '1' => Mage::helper('reports')->__('Specified'),
                ),
                'note'      => Mage::helper('reports')->__('Applies to Any of the Specified Order Payments'),
            ), 'to');
            
            $fieldset->addField('payment_methods', 'multiselect', array(
                'name'      => 'payment_methods',
                'values'    => $this->getActivPaymentMethods(),
                'display'   => 'none'
            ), 'show_order_paymentmethod');
            
            $fieldset->addField('show_card_type', 'select', array(
                'name'      => 'show_card_type',
                'label'     => Mage::helper('reports')->__('Card Type'),
                'options'   => array(
                    '0' => Mage::helper('reports')->__('Any'),
                    '1' => Mage::helper('reports')->__('Specified'),
                ),
                'note'      => Mage::helper('reports')->__('Applies to Any of the Specified Order Payments card type'),
            ), 'card_type');
            
            $fieldset->addField('card_type', 'multiselect', array(
                'name'      => 'card_type',
                'values'    => $this->getWppPeCcTypesAsOptionArray(),
                'display'   => 'none'
            ), 'show_card_type');
            
            $fieldset->addField('show_customer_group', 'select', array(
                'name'      => 'show_customer_group',
                'label'     => Mage::helper('reports')->__('Customer Group'),
                'options'   => array(
                    '0' => Mage::helper('reports')->__('Any'),
                    '1' => Mage::helper('reports')->__('Specified'),
                ),
                'note'      => Mage::helper('reports')->__('Applies to Any of the Specified Customer Group'),
            ), 'customer_group');
            
            
            $fieldset->addField('customer_group', 'multiselect', array(
                'name'      => 'customer_group',
                'values'    => $this->getCustomerGroupAsOptionArray(),
                'display'   => 'none'
            ), 'show_customer_group');
            
            
            //create order method
            $fieldset->addField('show_create_order', 'select', array(
                'name'      => 'show_create_order',
                'label'     => Mage::helper('reports')->__('Create order from'),
                'options'   => array(
                    '0' => Mage::helper('reports')->__('Any'),
                    '1' => Mage::helper('reports')->__('Specified'),
                ),
                'note'      => Mage::helper('reports')->__('Create order'),
            ), 'create_order_method');
            
            
            $fieldset->addField('create_order_method', 'multiselect', array(
                'name'      => 'create_order_method',
                'values'    => $this->getCreateOrderMethods(),
                'display'   => 'none'
            ), 'show_create_order');
            
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
                    'label'     => Mage::helper('reports')->__('Counterpoint Station Id'),
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
                        
                        ->addFieldMap("{$htmlIdPrefix}show_order_paymentmethod", 'show_order_paymentmethod')
                        ->addFieldMap("{$htmlIdPrefix}payment_methods", 'payment_methods')
                        ->addFieldDependence('payment_methods', 'show_order_paymentmethod', '1')
                        
                        ->addFieldMap("{$htmlIdPrefix}show_card_type", 'show_card_type')
                        ->addFieldMap("{$htmlIdPrefix}card_type", 'card_type')
                        ->addFieldDependence('card_type', 'show_card_type', '1')
                        
                        ->addFieldMap("{$htmlIdPrefix}show_customer_group", 'show_customer_group')
                        ->addFieldMap("{$htmlIdPrefix}customer_group", 'customer_group')
                        ->addFieldDependence('customer_group', 'show_customer_group', '1')
                        
                        ->addFieldMap("{$htmlIdPrefix}show_create_order", 'show_create_order')
                        ->addFieldMap("{$htmlIdPrefix}create_order_method", 'create_order_method')
                        ->addFieldDependence('create_order_method', 'show_create_order', '1')
                        
                        );
                }else{
                    $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                        ->addFieldMap("{$htmlIdPrefix}show_order_statuses", 'show_order_statuses')
                        ->addFieldMap("{$htmlIdPrefix}order_statuses", 'order_statuses')
                        ->addFieldDependence('order_statuses', 'show_order_statuses', '1')
                        
                        ->addFieldMap("{$htmlIdPrefix}show_order_paymentmethod", 'show_order_paymentmethod')
                        ->addFieldMap("{$htmlIdPrefix}payment_methods", 'payment_methods')
                        ->addFieldDependence('payment_methods', 'show_order_paymentmethod', '1')
                        
                        ->addFieldMap("{$htmlIdPrefix}show_card_type", 'show_card_type')
                        ->addFieldMap("{$htmlIdPrefix}card_type", 'card_type')
                        ->addFieldDependence('card_type', 'show_card_type', '1')
                        
                        ->addFieldMap("{$htmlIdPrefix}show_customer_group", 'show_customer_group')
                        ->addFieldMap("{$htmlIdPrefix}customer_group", 'customer_group')
                        ->addFieldDependence('customer_group', 'show_customer_group', '1')
                        
                        ->addFieldMap("{$htmlIdPrefix}show_create_order", 'show_create_order')
                        ->addFieldMap("{$htmlIdPrefix}create_order_method", 'create_order_method')
                        ->addFieldDependence('create_order_method', 'show_create_order', '1')
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
                $values[] = array('label'=>'Station 21' ,'value'=>'21');
                $values[] = array('label'=>'Station 22' ,'value'=>'22');
                $values[] = array('label'=>'Station 23' ,'value'=>'23');
                $values[] = array('label'=>'Station 24' ,'value'=>'24');
                $values[] = array('label'=>'Station 29' ,'value'=>'29');
            }else{
                $values[] = array('label'=>'Station 1' ,'value'=>'1');
                $values[] = array('label'=>'Station 2' ,'value'=>'2');
            }
        }
        return $values;
    }
    public function getActivPaymentMethods()
    {
        $resource = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = Mage::getSingleton('core/resource')->getTableName('sales/order_payment');
        $results = $resource->fetchAll("SELECT DISTINCT `method` FROM `$tableName`");
        
        $methods = array();
        
        foreach ($results as $paymentCode) {
            $paymentCode = $paymentCode['method'];
            // $title= $paymentCode['title'];
            //   $methods[$code] = Mage::getStoreConfig('payment/' . $paymentCode . '/title');
            $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
            $methods[$paymentCode] = array(
                'label'   => $paymentTitle,
                'value' => $paymentCode,
            );
        }
        
        return $methods;
        
    }
    public function getWppPeCcTypesAsOptionArray()
    {
        $model = Mage::getModel('payment/source_cctype')->setAllowedTypes(array('VI', 'MC', 'SM', 'SO', 'OT', 'AE','DI'));
        return $model->toOptionArray();
    }
    public function getCustomerGroupAsOptionArray()
    {
        $customer = Mage::getModel('customer/group')->getCollection();
        return $customer->toOptionArray();
    }
    
    //get create order when placed from
    private function getCreateOrderMethods(){
        $locations = array(
            array('label'=>'Website' ,'value' => 0),
            array('label'=>'Counterpoint' ,'value' => 1)
        );
        
        $user = Mage::getSingleton('admin/session')->getUser();
        if ($user != null){
            $userRole = $user->getRole()->getData();
            $roleID = $userRole["role_id"];
            if($roleID == 1){
                $locations[] = array('label'=>'Teamwork' ,'value' => 2);
            }
        }
        return $locations;
    }
}
