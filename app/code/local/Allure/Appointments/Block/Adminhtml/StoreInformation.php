<?php
class Allure_Appointments_Block_Adminhtml_StoreInformation extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    public function __construct()
    {
        // create columns
        $this->addColumn('store', array(
            'label' => Mage::helper('adminhtml')->__('Store'),
            'size' => 28,
        ));
        $this->addColumn('name', array(
            'label' => Mage::helper('adminhtml')->__('Appear Name'),
            'size' => 28
        ));
        $this->addColumn('time', array(
        		'label' => Mage::helper('adminhtml')->__('Time Pref'),
        		'size' => 28
        ));
        $this->addColumn('timezone', array(
        		'label' => Mage::helper('adminhtml')->__('Timezone'),
        		'size' => 28
        ));
        $this->addColumn('timezoneabbr', array(
        		'label' => Mage::helper('adminhtml')->__('Timezone Abbrevation'),
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
        
        if ($columnName === "store"){
        	
        	$countryStr = "";
        	$allStores = Mage::app()->getStores();
        	foreach ($allStores as $_eachStoreId => $val)
        	{
        		$_storeName = Mage::app()->getStore($_eachStoreId)->getName();
        		$_storeId = Mage::app()->getStore($_eachStoreId)->getId();
        		$countryStr .= '<option value="'.$_storeId.'">'.$_storeName .'</option>';
        	}
            return '<select name="' . $inputName . '" style="width:150px;">'.$countryStr.'</select>';
        }  elseif ($columnName === "time") {
        	
        	$countryStr .= '<option value="12"> 12 Hrs</option>';
        	$countryStr .= '<option value="24"> 24 Hrs</option>';
        	return '<select name="' . $inputName . '" style="width:100px;">'.$countryStr.'</select>';
        }elseif ($columnName === "timezone") {
        	
        
        	$countryStr = "";
   			$timezoneList = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        	foreach (Mage::app()->getLocale()->getOptionTimezones() as $_eachTimeZoneId => $val)
        	{
        		$zoneStr .= '<option value="'.$val[value].'">'.$val[label].'</option>';
        	}
        	return '<select name="' . $inputName . '" style="width:150px;">'.$zoneStr.'</select>';
        }
        elseif($columnName === "timezoneabbr"){
        	return '<input type="text" name="' . $inputName . '" style="width:170px;"/>';
        }
        else{
        	return '<input type="text" name="' . $inputName . '" style="width:170px;"/>';
        }
    }
}