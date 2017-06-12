<?php

class Ebizmarts_BakerlooLocation_Block_Adminhtml_Pos_Store extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    /**
     * Block constructor
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_pos_store';
        $this->_blockGroup = 'bakerloo_location';
        $this->_headerText = Mage::helper('bakerloo_location')->__('Stores');

        parent::__construct();
    }
}
