<?php
class Allure_PrivateSale_LoginController extends Mage_Core_Controller_Front_Action{

    public function indexAction()
    {
        if(!$this->helper()->isEnabled()) {
            Mage::getSingleton('core/session')->setPrivateSaleValidUser(false);
            $this->_redirectUrl(Mage::getUrl());
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Maria Tash Private Sale'));
        $this->renderLayout();
    }
    public function verifyAction()
    {
        $username=$this->helper()->getUsername();
        $password=$this->helper()->getPassword();

        $data=$this->getRequest()->getPost();
        if(!empty($data))
        {
            if($data['username']==$username && $data['password']==$password)
            {
                Mage::getSingleton('core/session')->setPrivateSaleValidUser(true);
                $categoryLink = Mage::getModel("catalog/category")->load($this->helper()->getCategory())->getUrl();
                $this->_redirectUrl($categoryLink);
            }
            else {
                Mage::getSingleton("core/session")->addError("Incorrect username or password");
                $this->_redirectReferer();
            }

        }else{
            Mage::getSingleton("core/session")->addError("Username and password required");
            $this->_redirectReferer();
        }
    }
    private function helper()
    {
        return Mage::helper('privatesale');
    }

}
