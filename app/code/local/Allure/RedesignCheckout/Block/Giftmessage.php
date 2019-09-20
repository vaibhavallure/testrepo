<?php
/**
 * 
 * @author allure
 *
 */
require_once ('app/code/core/Mage/GiftMessage/Block/Message/Inline.php');

class Allure_RedesignCheckout_Block_Giftmessage extends Mage_GiftMessage_Block_Message_Inline
{
    protected $_item;
    
    public function setItem($item){
        $this->_item = $item;
    }
    
    public function getItem(){
        return $this->_item;
    }
}

