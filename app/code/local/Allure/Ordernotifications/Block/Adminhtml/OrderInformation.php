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
        $this->addColumn('timeinterval', array(
            'label' => Mage::helper('adminhtml')->__('Time Interval'),
            'size' => 28
        ));
        $this->addColumn('timespan', array(
        		'label' => Mage::helper('adminhtml')->__('Time Span'),
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
        $this->addColumn('store', array(
        		'label' => Mage::helper('adminhtml')->__('Store'),
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
            return '<select name="' . $inputName . '" style="width:160px;">'.$statusStr.'</select>';
        }  elseif ($columnName === "timeinterval") {
        
        	return '<input type="text" name="' . $inputName . '" style="width:60px;"/>';

        } elseif ($columnName === "timespan") {
        	$statusList=Mage::helper('allure_ordernotifications')->getTimeSpanArray();
        	foreach ($statusList as $key => $value)
        	{
        		$statusStr .= '<option value="'.$key.'">'.$value .'</option>';
        	}
        	return '<select name="' . $inputName . '" style="width:70px;">'.$statusStr.'</select>';
        	

        }elseif ($columnName === "email") {
        	return '<input type="text" name="' . $inputName . '" style="width:170px;"/>';
        
        }elseif ($columnName === "store") {
        	return '<input type="text" name="' . $inputName . '" style="width:55px;"/>';
        }
        else{
        	$statusStr = "";
        	$statusStr .= '<option value="1">Yes</option>';
        	$statusStr .= '<option value="0">No</option>';
        	return '<select name="' . $inputName . '" style="width:70px;">'.$statusStr.'</select>';
        }
        
    }
}