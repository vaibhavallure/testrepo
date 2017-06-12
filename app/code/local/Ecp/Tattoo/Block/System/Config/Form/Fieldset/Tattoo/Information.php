<?php

/**
 * Ecp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecp EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   Ecp
 * @package    Ecp_Adminhtml
 * @copyright  Copyright (c) 2010 Ecp (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Ecp Adminhtml extension
 *
 * @category   Ecp
 * @package    Ecp_Adminhtml
 */
class Ecp_Tattoo_Block_System_Config_Form_Fieldset_Tattoo_Information extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

    protected $_dummyElement;
    protected $_fieldRenderer;
    protected $_values;

    public function render(Varien_Data_Form_Element_Abstract $element) {
        $html = $this->_getHeaderHtml($element);

        $fields = array(
            /*array('type' => 'text', 'name' => 'name', 'label' => $this->__('Contact Name'), 'class' => 'required-entry'),
            array('type' => 'text', 'name' => 'email', 'label' => $this->__('Contact Email'), 'class' => 'required-entry validate-email'),
            array('type' => 'text', 'name' => 'subject', 'label' => $this->__('Subject'), 'class' => 'required-entry'),
            array('type' => 'select', 'name' => 'reason', 'label' => $this->__('Reason'), 'values' => $this->_getReasons(), 'class' => 'required-entry', 'onchange' => 'toggleReason();'),
            array('type' => 'text', 'name' => 'other_reason', 'label' => $this->__('Other Reason'), 'class' => 'required-entry', 'onchange' => 'toggleReason();'),*/
            array('type' => 'image', 'name' => 'banner', 'label' => $this->__('Banner'), 'class' => 'required-entry'),
            array('type' => 'textarea', 'name' => 'message', 'label' => $this->__('Celebrating Text'), 'class' => 'required-entry'),
            array('type' => 'label', 'name' => 'send', 'after_element_html' => '<div class="right"><button type="button" class="scalable save" onclick="mageworxSupport();">' . $this->__('Send') . '</button></div><div class="notice" id="ajax-response"></div>'),
        );
        foreach ($fields as $field) {
            $html.= $this->_getFieldHtml($element, $field);
        }
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    protected function _getFieldRenderer() {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_fieldRenderer;
    }

    protected function _getFooterHtml($element) {
        $html = parent::_getFooterHtml($element);
        $html = '<h4>' . $this->__('Installed Ecp Extensions') . '</h4>' . $html;

        return $html;
    }

    protected function _getFieldHtml($fieldset, $field) {
        $type = $field['type'];
        unset($field['type']);
        $field = $fieldset->addField($field['name'], $type, $field)->setRenderer($this->_getFieldRenderer());

        return $field->toHtml();
    }

}
