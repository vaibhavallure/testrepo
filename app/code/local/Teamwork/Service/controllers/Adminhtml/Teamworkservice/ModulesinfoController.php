<?php

class Teamwork_Service_Adminhtml_Teamworkservice_ModulesinfoController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $allModules = Mage::getConfig()->getNode('modules')->children();
        $modulesInfo = array();
        foreach ($allModules as $moduleName => $moduleSettings)
        {
            if (strpos($moduleName, 'Teamwork') !== false)
            {
                $modulesInfo[$moduleName] = array(
                    'version' => (string)$moduleSettings->version,
                    'active'  => $moduleSettings->is('active')
                );
            }
        }

        Mage::register('teamwork_modules_info', $modulesInfo);

        $this->loadLayout()->_setActiveMenu('teamwork_service');
        $contentBlock = $this->getLayout()->createBlock('teamwork_service/adminhtml_modulesinfo');
        $this->_addContent($contentBlock);
        $this->renderLayout();
    }
    
    protected function _isAllowed(){
        return true;
    }
}