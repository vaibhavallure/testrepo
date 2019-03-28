<?php
class Allure_Translations_Block_LocaleMap extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $magentoOptions;

    public function __construct()
    {
        // create columns
        $this->addColumn('countryCode', array(
            'label' => Mage::helper('adminhtml')->__('Country'),
            'size' => 28,
        ));
        $this->addColumn('localeCode', array(
            'label' => Mage::helper('adminhtml')->__('Locale'),
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
        
        if ($columnName === "countryCode"){
            $countryStr = "";
            $countryArr = Mage::helper('allure_geolocation')->getCountryList();
            foreach($countryArr as $code => $country){
                $countryStr .= '<option value="'.$code.'">'.$country.'</option>';
            }            
            return '<select name="' . $inputName . '">'.$countryStr.'</select>';
        } else if ($columnName === "localeCode"){
            $localeStr = "";
            $localeList=Mage::app()->getLocale()->getOptionLocales();
            foreach($localeList as $key => $data){
                $localeStr .= '<option value="'.$data['value'].'">'.$data['label'].'</option>';
            }
            return '<select name="' . $inputName . '">'.$localeStr.'</select>';
        } else {
            return '<input type="text" name="' . $inputName . '" value="#{' . $columnName . '}" ' . ($column['size'] ? 'size="' . $column['size'] . '"' : '') . '/>';
        }
    }
}