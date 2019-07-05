<?php

class Allure_CustomerLoginMonitor_Adminhtml_LoginMonitorController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('allure/customerloginmonitor')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('allure'), Mage::helper('adminhtml')->__('Allure'))
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Login Monitor'), Mage::helper('adminhtml')->__('Login Monitor'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__("Login Monitor"));
        $this->_initAction()->_addContent ( $this->getLayout ()->createBlock ( "customerloginmonitor/adminhtml_login_grid" ) )
            ->renderLayout ();
    }
}