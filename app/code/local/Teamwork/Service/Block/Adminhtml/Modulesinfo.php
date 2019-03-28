<?php

class Teamwork_Service_Block_Adminhtml_Modulesinfo extends Mage_Adminhtml_Block_Widget_View_Container
{

    public function __construct()
    {
        $this->_headerText = Mage::helper('teamwork_service')->__('About');

        parent::__construct();

        $this->_removeButton('edit');
        $this->_removeButton('back');
    }

    public function getViewHtml()
    {
        $result = 'Sorry, internal error occured';

        if ($modulesInfo = Mage::registry('teamwork_modules_info'))
        {
            $result = 'You have next Teamwork modules: <ul>';
            foreach($modulesInfo as $code => $moduleInfo)
            {
                if ($moduleInfo['active'])
                {
                    // if module is active, but version is absent, it means module is absent but XML in app/etc/modules exists
                    $info = $moduleInfo['version'] ? "active, version {$moduleInfo['version']} " : "absent (only module XML exists in app/etc/modules)";
                }
                else
                {
                    $info = 'disabled';
                }

                $result .= "<li style='list-style-type: disc; margin-left:2em'>$code - $info</li>";
            }

            $result .= '</ul>';
        }

        return $result;
    }
}