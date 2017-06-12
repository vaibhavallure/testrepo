<?php
class Allure_Core_Block_Extensions extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_dummyElement;
    protected $_fieldRenderer;
    protected $_values;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        sort($modules);

        foreach ($modules as $moduleName) {
            if (strstr($moduleName, 'Allure_') === false) {
                continue;
            }

            if ((string)Mage::getConfig()->getModuleConfig($moduleName)->is_system == 'true') {
                continue;
            }

            $html.= $this->_getFieldHtml($element, $moduleName);
        }
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_fieldRenderer;
    }

    protected function _getFieldHtml($fieldset, $moduleCode)
    {
        $currentVer = Mage::getConfig()->getModuleConfig($moduleCode)->version;
        
        if (!$currentVer) {
            return '';
        }

         // in case we have no data in the RSS
        $moduleName = (string)Mage::getConfig()->getNode('modules/' . $moduleCode . '/name');
        if (!$moduleName) {
            $moduleName = substr($moduleCode, strpos($moduleCode, '_') + 1);
        }

        $moduleName = $moduleName;

        $field = $fieldset->addField($moduleCode, 'label', array(
            'name'  => 'allure_module-'.$moduleCode,
            'comment'  => $moduleName,
            'label' => "<strong>$moduleCode</strong>",
            'value' => $currentVer,
        ))->setRenderer($this->_getFieldRenderer());

        return $field->toHtml();
    }
}