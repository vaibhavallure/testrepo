<?php
/**
 * 
 * @author allure
 *
 */
class Allure_CustomUrl_Block_Adminhtml_Customurl_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'allure_customurl';
        $this->_controller = 'adminhtml_customurl';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('allure_customurl')->__('Save')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('allure_customurl')->__('Delete')
        );
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('allure_customurl')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save',
            ),
            -100
        );
        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * get the edit form header
     *
     * @access public
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_customurl') && Mage::registry('current_customurl')->getId()) {
            return Mage::helper('allure_customurl')->__(
                "Edit Custom Url '%s'",
                $this->escapeHtml(Mage::registry('current_customurl')->getId())
            );
        } else {
            return Mage::helper('allure_customurl')->__('Add New Custom Url');
        }
    }
}
