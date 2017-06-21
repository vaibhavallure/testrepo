<?php
class Allure_Ordernotifications_Block_Adminhtml_OrderInformation extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    public function __construct()
    {
        // create columns
        $this->addColumn('order_status', array(
            'label' => Mage::helper('adminhtml')->__('Order Status'),
            'size' => 28,
        ));
        $this->addColumn('no_of_weeks', array(
            'label' => Mage::helper('adminhtml')->__('No of Weeks'),
            'size' => 28
        ));
        $this->addColumn('email', array(
        		'label' => Mage::helper('adminhtml')->__('Email'),
        		'size' => 28
        ));
        $this->addColumn('enabled', array(
        		'label' => Mage::helper('adminhtml')->__('Enabled'),
        		'size' => 28,
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
        
        if ($columnName === "order_status"){
        	
        	$statusStr = "";
        	$allStores = Mage::app()->getStores();
        	$statusList=Mage::getModel('sales/order_status')->getResourceCollection()->getData();
        	foreach ($statusList as $key => $list)
        	{
        		$statusStr .= '<option value="'.$list['status'].'">'.$list['label'] .'</option>';
        	}
            return '<select name="' . $inputName . '" style="width:200px;">'.$statusStr.'</select>';
        }  elseif ($columnName === "no_of_weeks") {
        
        	return '<input type="text" name="' . $inputName . '" style="width:60px;"/>';

        }elseif ($columnName === "email") {
        	return '<input type="text" name="' . $inputName . '" style="width:170px;"/>';
        }
        else{
        	$statusStr = "";
        	$statusStr .= '<option value="1">Yes</option>';
        	$statusStr .= '<option value="0">No</option>';
        	return '<select name="' . $inputName . '" style="width:150px;">'.$statusStr.'</select>';
        }
        
    }
}