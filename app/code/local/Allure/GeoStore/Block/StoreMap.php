<?php
class Allure_GeoStore_Block_StoreMap extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $magentoOptions;

    public function __construct()
    {
        // create columns
        $this->addColumn('countryCode', array(
            'label' => Mage::helper('adminhtml')->__('Country'),
            'size' => 28,
        ));
        
        $this->addColumn('store', array(
            'label' => Mage::helper('adminhtml')->__('Store'),
            'size' => 28
        ));
        
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Line');

        parent::__construct();
        
        $this->setTemplate('allure/geolocation/system/config/form/field/store_map.phtml');

    }

	protected function _renderCellTemplate($columnName)
    {           
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }
        $column = $this->_columns[$columnName];
        $inputName = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        
        if ($columnName === "countryCode"){
            $countryStr = "";
            $countryArr = Mage::helper('allure_geolocation')->getCountryList();
            foreach($countryArr as $code => $country){
                $countryStr .= '<option value="'.$code.'">'.$country.'</option>';
            }            
            return '<select name="' . $inputName . '">'.$countryStr.'</select>';
        } else if($columnName === "store"){
            $storeStr = "";
            foreach (Mage::app()->getWebsites() as $website) {  
                foreach ($website->getGroups() as $group) {
                    $stores = $group->getStores();
                    $storeStr .= '<optgroup label="'.$group->getName().'">';
                    foreach ($stores as $store) {
                        $storeStr .= '<option value="'.$store->getId().'">'.$store->getName().'</option>';
                    }
                    $storeStr .= '</optgroup>';
                }
            }        
            return '<select name="' . $inputName . '">'.$storeStr.'</select>';
        } else {
            return '<input type="text" name="' . $inputName . '" value="#{' . $columnName . '}" ' . ($column['size'] ? 'size="' . $column['size'] . '"' : '') . '/>';
        }
    }
}