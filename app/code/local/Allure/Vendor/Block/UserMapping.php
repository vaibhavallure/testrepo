<?php
class Allure_Vendor_Block_UserMapping extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    public function __construct()
    {
        // create columns
        $this->addColumn('vendor', array(
            'label' => Mage::helper('adminhtml')->__('Vendor'),
            'size' => 28,
        ));
        $this->addColumn('user', array(
            'label' => Mage::helper('adminhtml')->__('User'),
            'size' => 28
        ));
        
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Line');

        parent::__construct();
        
        $this->setTemplate('allure/geolocation/system/config/form/field/currency_map.phtml');

    }

    protected function _renderCellTemplate($columnName)
    {           
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }
        $column = $this->_columns[$columnName];
        $inputName = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        
        if ($columnName === "vendor"){
            $countryStr = "";
            //$countryArr = Mage::helper('allure_geolocation')->getCountryList();
            $atributeCode = 'primary_vendor';
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$atributeCode);
            $options = $attribute->getSource()->getAllOptions();
            foreach($options as $key => $value){
                $countryStr .= '<option value="'.$value['value'].'">'.$value['label'] .'</option>';
            }            
            return '<select name="' . $inputName . '">'.$countryStr.'</select>';
        }  else {
        	
        	$options = $this->getVendorUsers();
        	foreach($options as $user){
        		$userStr .= '<option value="'.$user->getUserId().'">'.$user->getUsername() .'</option>';
        	}
        	return '<select name="' . $inputName . '">'.$userStr.'</select>';
        }
    }
    public function getVendorUsers()
    {
    	$parentId=Mage::getModel('admin/role')->load('Vendor','role_name')->getRoleId();
    	$collection=Mage::getModel('admin/user')->getCollection();
    	$collection->getSelect()->joinLeft('admin_role', 'admin_role.user_id = main_table.user_id', array('parent_id'));
    	$collection->getSelect()->where("admin_role.parent_id = '$parentId'");
    
    	return $collection;
    }
}