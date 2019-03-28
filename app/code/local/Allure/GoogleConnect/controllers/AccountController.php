<?php

class Allure_GoogleConnect_AccountController extends Mage_Core_Controller_Front_Action
{
    
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }
        
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }    

    public function indexAction()
    {        
        $userInfo = Mage::getSingleton('allure_googleconnect/userinfo')
                ->getUserInfo();
        
        Mage::register('allure_googleconnect_userinfo', $userInfo);
        
        $this->loadLayout();
        $this->renderLayout();
    }
    
}
