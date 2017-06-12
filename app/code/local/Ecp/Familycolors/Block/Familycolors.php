<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Ecp_Familycolors_Block_Familycolors extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getFamilyColors()
    {
        return Mage::getModel('ecp_familycolors/familycolors')->getCollection();
    }
}