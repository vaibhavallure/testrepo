<?php 
class Allure_Shipping_Block_Adminhtml_Methods extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $magentoOptions;
    /**
     * Add custom config field columns, set template, add values.
     */
    public function __construct()
    {
        /** @var Allure_Shipping_Helper_Data $helper */
        $helper = Mage::helper('allure_shipping');
        
        $this->addColumn('shipping_carrier', array(
            'style' => 'width:100px',
            'label' => $helper->__('Shipping Method'),
        ));
        
        $this->addColumn('used_in', array(
            'style' => 'width:100px',
            'label' => $helper->__('Used In'),
        ));
        
        $this->addColumn('customer_group', array(
            'style' => 'width:80px',
            'label' => $helper->__('Customer Group'),
        ));
        
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Line');
        
        parent::__construct();
        $this->setTemplate('allure/shipping/system/config/form/field/mehods_mapping.phtml');
    }
    
    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }
        $column = $this->_columns[$columnName];
        $inputName = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        
        if ($columnName === "shipping_carrier"){
            $shippingCarriers = Mage::getSingleton('shipping/config')->getAllCarriers();
            $carrierStr = "";
            foreach ($shippingCarriers as $carrierCode => $carrierInstance){
                $carrierStr .= '<option value="'.$carrierCode.'">'.strtoupper($carrierCode).'</option>';
            }
            return '<select name="' . $inputName . '">'.$carrierStr.'</select>';
        } else if ($columnName === "used_in"){
            $arrUsedIn = array("frontend" => "Front", "adminhtml" => "Admin Panel");
            $usedInStr = "";
            foreach($arrUsedIn as $code => $label){
                $usedInStr .= '<option value="'.$code.'">'.$label.'</option>';
            }
            return '<select name="' . $inputName . '[]" multiple>'.$usedInStr.'</select>';
        }else if ($columnName === "customer_group"){
            $groups = Mage::getModel('customer/group')->getCollection();
            $groupsStr = "";
            foreach ($groups as $Group) {
                $groupsStr .= '<option value="'.$Group->getCustomerGroupId().'">'.$Group->getCustomerGroupCode().'</option>';
            }
            return '<select name="' . $inputName . '[]" multiple>'.$groupsStr.'</select>';
        }else {
            return '<input type="text" name="' . $inputName . '" value="#{' . $columnName . '}" ' . ($column['size'] ? 'size="' . $column['size'] . '"' : '') . '/>';
        }
    }
}