<?php
class Allure_MyAccount_Block_Adminhtml_Customerorderstatus extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    public function __construct()
    {
        // create columns
        $this->addColumn('order_state', array(
            'label' => Mage::helper('adminhtml')->__('State'),
            'size' => 28,
        ));
        $this->addColumn('label', array(
            'label' => Mage::helper('adminhtml')->__('Label'),
            'size' => 28
        ));
        
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Line');

        parent::__construct();
        
        $this->setTemplate('appointments/system/config/form/field/store_fields.phtml');

    }

    protected function _renderCellTemplate($columnName)
    {           
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }
        $column = $this->_columns[$columnName];
        $inputName = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        
        if ($columnName === "order_state"){
        	
        	$stateStr = "";
        	$orderStateArr = Mage::getSingleton('sales/order_config')
        	                   ->getStates();
        	$status        = Mage::getSingleton('sales/order_config')
        	                   ->getStatuses();
        	
        	foreach ($status as $key=>$value){
        	    if(!array_key_exists($key, $orderStateArr)){
        	        $orderStateArr[$key] = $value;
        	   }
        	}
        	                   
        	foreach ($orderStateArr as $state => $val)
        	{
        	    $stateStr .= '<option value="'.$state.'">'.$val .'</option>';
        	}
        	return '<select name="' . $inputName . '" style="width:150px;">'.$stateStr.'</select>';
        }  
        else{
        	return '<input type="text" name="' . $inputName . '" style="width:170px;"/>';
        }
    }
}