<?php

class Ecp_Backup_Block_Adminhtml_Request extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

    public function render(Varien_Data_Form_Element_Abstract $element) {
        $html = $this->_getHeaderHtml($element);
        $html.= $this->_getFieldHtml($element);
        $html .= $this->_getFooterHtml($element);
        return $html;
    }

    protected function _getFieldHtml() {
        $content = '<p>Haz click para descarga. <a href="' . Mage::helper('adminhtml')->getUrl('ecp_backup/adminhtml_backup') . 
                    '">Click Here</a></p>';
        return $content;
    }
}