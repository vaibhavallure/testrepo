<?php

class Allure_Ajaxify_Block_Rewrite_Messages extends ECP_Ajaxify_Block_Messages
{   
    public function _prepareLayout()
    {  
       //Mage::log("hiiiii",Zend_log::DEBUG,'abc',true);
       //Mage::log("area : ".Mage::app()->getStore()->isAdmin(),Zend_log::DEBUG,'abc',true);
       foreach ($this->_usedStorageTypes as $class_name) {
            $storage = Mage::getSingleton($class_name);
            if ($storage) {
                $this->addMessages($storage->getMessages(true));
            }
        }
        Mage_Core_Block_Template::_prepareLayout();
       /*  if(!Mage::app()->getStore()->isAdmin())
        	Mage::app()->setCurrentStore($_SESSION['allure']); */
    }
}
