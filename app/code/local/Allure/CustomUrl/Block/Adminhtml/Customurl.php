<?php
/**
 * 
 * @author allure
 *
 */
class Allure_CustomUrl_Block_Adminhtml_CustomUrl extends Mage_Adminhtml_Block_Widget_Grid_Container {
    
    public function __construct()
    {
        $this->_blockGroup = 'allure_customurl';
        $this->_controller = 'adminhtml_customurl';
        $this->_headerText = $this->__('Custom Urls');
        
        parent::__construct();
    }
}