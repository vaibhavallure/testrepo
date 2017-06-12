<?php

class Ebizmarts_BakerlooLocation_Block_Adminhtml_Pos_Store_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId   = 'id';
        $this->_controller = 'adminhtml_pos_store';
        $this->_blockGroup = 'bakerloo_location';

        parent::__construct();
    }

    public function getHeaderText()
    {
        if ($this->getLocation()->getId()) {
            return Mage::helper('bakerloo_location')->__("Edit location");
        } else {
            return Mage::helper('bakerloo_location')->__('New location');
        }
    }

    /**
     * Get object from registry.
     *
     */
    public function getLocation()
    {
        return Mage::registry('poslocation');
    }
}
