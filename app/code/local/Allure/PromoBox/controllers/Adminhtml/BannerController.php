<?php

class Allure_PromoBox_Adminhtml_BannerController extends Mage_Adminhtml_Controller_action {


    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
    function _isAllowed()
    {
        return true;
    }
}