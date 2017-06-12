<?php
/**
 * Entrepids
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize for your
 * needs please refer to Entrepids Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Customercarenavleft
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Customercarenavleft
 *
 * @category    Ecp
 * @package     Ecp_Customercarenavleft
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Customercarenavleft_Block_Adminhtml_Customercarenavleft_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'ecp_customercarenavleft';
        $this->_controller = 'adminhtml_customercarenavleft';
        
        $this->_updateButton('save', 'label', Mage::helper('ecp_customercarenavleft')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('ecp_customercarenavleft')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('customercarenavleft_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'customercarenavleft_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'customercarenavleft_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('customercarenavleft_data') && Mage::registry('customercarenavleft_data')->getId() ) {
            return Mage::helper('ecp_customercarenavleft')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('customercarenavleft_data')->getTitle()));
        } else {
            return Mage::helper('ecp_customercarenavleft')->__('Add Item');
        }
    }
}