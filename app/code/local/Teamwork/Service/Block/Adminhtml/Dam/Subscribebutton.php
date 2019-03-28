<?php

class Teamwork_Service_Block_Adminhtml_Dam_Subscribebutton extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $url = $this->getUrl('adminhtml/teamworkservice_dam/subscribe');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Subscribe')
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        $url = $this->getUrl('adminhtml/teamworkservice_dam/unsubscribe');

        $html .= "&nbsp;&nbsp;&nbsp;&nbsp;" . $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Unsubscribe')
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }
}
