<?php
class Doddle_Returns_Block_Adminhtml_TestApiButton extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setId('doddle-returns-api-test')
            ->setLabel('Test API Connection')
            ->setOnClick($this->getTestAPIScript())
            ->toHtml();

        return $html;
    }

    /**
     * @return string
     */
    protected function getTestAPIScript()
    {
        return sprintf(
            "new DoddleApiTest('%s', '%s')",
            Mage::helper('doddle_returns')->__('API credentials successfully authenticated'),
            Mage::helper('doddle_returns')->__('Invalid API credentials')
        );
    }
}
