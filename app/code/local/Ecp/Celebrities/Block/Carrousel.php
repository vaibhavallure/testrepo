<?php

//Carrousel
class Ecp_Celebrities_Block_Carrousel extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {

    protected function _prepareLayout() {
        $head = $this->getLayout()->getBlock('head');
        $head->addJs('jquery/jquery.carouFredSel-6.1.0-packed.js');
        return parent::_prepareLayout();
    }

    protected function _toHtml() {
        $this->setTemplate('ecp/celebrities/carrousel.phtml');
        return parent::_toHtml();
    }

    protected function getCelebrities() {
        $collection = Mage::getModel('ecp_celebrities/celebrities')->getCollection();
        $collection->getSelect()->order('ordering ASC');
        $collection->addFieldToFilter('status',1);

        return $collection;
    }
    
    public function getCurrentCelebrityId(){
        return Mage::registry('celebrities')->getId();
    }

}
