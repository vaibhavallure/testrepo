<?php
/**
 * Allure_InstaCatalog
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @copyright   Copyright© 2016, Allure Inc
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Team Allure <extensions@allureinc.co>
 */
/**
 * Feed admin edit form
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @author      Team Allure <extensions@allureinc.co>
 */
class Allure_InstaCatalog_Block_Adminhtml_Feed_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
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
        $this->_blockGroup = 'allure_instacatalog';
        $this->_controller = 'adminhtml_feed';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('allure_instacatalog')->__('Save Feed')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('allure_instacatalog')->__('Delete Feed')
        );
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('allure_instacatalog')->__('Save And Continue Edit'),
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
        if (Mage::registry('current_feed') && Mage::registry('current_feed')->getId()) {
            return Mage::helper('allure_instacatalog')->__(
                "Edit Feed '%s'",
                $this->escapeHtml(Mage::registry('current_feed')->getMediaId())
            );
        } else {
            return Mage::helper('allure_instacatalog')->__('Add Feed');
        }
    }
}
