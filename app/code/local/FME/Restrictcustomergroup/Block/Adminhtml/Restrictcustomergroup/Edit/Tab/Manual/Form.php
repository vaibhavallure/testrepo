<?php

class FME_Restrictcustomergroup_Block_Adminhtml_Restrictcustomergroup_Edit_Tab_Manual_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        return parent::_prepareForm();
    }
    
    public function _construct() {

        $this->setTemplate('restrictcustomergroup/manual.phtml');
    }

}
