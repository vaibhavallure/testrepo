<?php

class Allure_Mana_Helper_Core_Layout extends Mana_Core_Helper_Layout {
    /**
     * @param Mage_Core_Block_Abstract $block
     */
    public function delayPrepareLayout($block, $sortOrder = 0) {
    	
    	$params = Mage::app()->getRequest()->getParams();
    	$aw_ajaxcatalog = false;
    	if(!empty($params) && array_key_exists('aw_ajaxcatalog', $params)){
    		$aw_ajaxcatalog = true;
    	}
    	
        if ( $aw_ajaxcatalog || $this->_delayedLayoutIsBeingProcessed || Mage::registry('m_page_is_being_rendered')) {
            $block->delayedPrepareLayout();
        }
        else {
            $this->_delayPrepareLayoutBlocks[$block->getNameInLayout()] = compact('block', 'sortOrder');
        }
    }
    
}